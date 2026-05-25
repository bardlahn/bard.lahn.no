#!/usr/bin/env python3
"""
index_md.py — Re-indexes MD files into _index.md based on frontmatter.

Usage:
    python index_md.py <directory>

The script reads _index.md in the given directory, then scans all other .md
files. Files whose frontmatter "type" matches the index's "sub-type" are
collected and written into sub-items: in _index.md.

- sub-indexed is updated to today's date (Y-m-d)
- All dates in sub-items are stored as plain strings in Y-m-d format
- The "type" field is excluded from sub-items entries
- sub-items are sorted according to sub-sort (e.g. "date=descending")
- The index file's other header fields are preserved unchanged

Parsing strategy (automatic):
  - If PyYAML is installed  →  used for parsing (handles any nesting depth)
  - If PyYAML is not found  →  built-in fallback parser (handles ~3 levels)
The custom serialiser always runs so output style stays consistent.

Install PyYAML (recommended):
    pip install pyyaml
"""

import os
import sys
import re
from datetime import date, datetime

# ---------------------------------------------------------------------------
# PyYAML — used for parsing when available, skipped otherwise
# ---------------------------------------------------------------------------

try:
    import yaml as _yaml
    import re as _re

    # Override PyYAML's bool resolver so 'no'/'yes'/'on'/'off' stay as strings.
    # YAML 1.1 (used by PyYAML) treats these as booleans, but frontmatter often
    # uses them as plain strings (e.g. language: 'no').
    class _FrontmatterLoader(_yaml.SafeLoader):
        pass

    _FrontmatterLoader.yaml_implicit_resolvers = {
        k: [(tag, regexp) for tag, regexp in resolvers
            if tag != 'tag:yaml.org,2002:bool']
        for k, resolvers in _yaml.SafeLoader.yaml_implicit_resolvers.items()
    }
    _FrontmatterLoader.add_implicit_resolver(
        'tag:yaml.org,2002:bool',
        _re.compile(r'^(?:true|false)$', _re.IGNORECASE),
        list('tTfF')
    )

    def _load_yaml(raw):
        return _yaml.load(raw, Loader=_FrontmatterLoader) or {}

    USING_PYYAML = True

except ImportError:
    _load_yaml = None
    USING_PYYAML = False


# ---------------------------------------------------------------------------
# Fallback: minimal YAML-ish parser (no dependencies, ~3 levels deep)
# ---------------------------------------------------------------------------

def _parse_yaml_block(text, indent):
    result = {}
    lines = text.split('\n')
    i = 0
    while i < len(lines):
        line = lines[i]
        if not line.strip() or line.strip().startswith('#'):
            i += 1
            continue
        leading = len(line) - len(line.lstrip())
        if leading < indent:
            break
        if leading > indent:
            i += 1
            continue
        stripped = line.strip()
        if ':' in stripped:
            key, _, val = stripped.partition(':')
            key = key.strip()
            val = val.strip()
            if val == '' or val is None:
                sub_lines = []
                j = i + 1
                while j < len(lines):
                    sub_line = lines[j]
                    sub_leading = len(sub_line) - len(sub_line.lstrip()) if sub_line.strip() else indent + 2
                    if sub_line.strip() == '':
                        sub_lines.append(sub_line)
                        j += 1
                        continue
                    if sub_leading <= indent:
                        break
                    sub_lines.append(sub_line)
                    j += 1
                sub_text = '\n'.join(sub_lines)
                first_content = next((l.strip() for l in sub_lines if l.strip()), '')
                if first_content.startswith('- '):
                    result[key] = _parse_yaml_list(sub_text, indent + 2)
                else:
                    result[key] = _parse_yaml_block(sub_text, indent + 2)
                i = j
                continue
            else:
                result[key] = _parse_scalar(val)
        i += 1
    return result


def _parse_yaml_list(text, indent):
    items = []
    lines = text.split('\n')
    i = 0
    while i < len(lines):
        line = lines[i]
        if not line.strip():
            i += 1
            continue
        leading = len(line) - len(line.lstrip())
        if leading < indent - 2:
            break
        stripped = line.strip()
        if stripped.startswith('- '):
            val = stripped[2:].strip()
            if val == '':
                sub_lines = []
                j = i + 1
                while j < len(lines):
                    sub_line = lines[j]
                    sub_leading = len(sub_line) - len(sub_line.lstrip()) if sub_line.strip() else indent + 2
                    if sub_line.strip() == '':
                        sub_lines.append(sub_line)
                        j += 1
                        continue
                    if sub_leading <= indent:
                        break
                    sub_lines.append(sub_line)
                    j += 1
                items.append(_parse_yaml_block('\n'.join(sub_lines), indent + 2))
                i = j
                continue
            elif ':' in val:
                k, _, v = val.partition(':')
                k = k.strip()
                v = v.strip()
                if v == '':
                    sub_lines = []
                    j = i + 1
                    while j < len(lines):
                        sub_line = lines[j]
                        sub_leading = len(sub_line) - len(sub_line.lstrip()) if sub_line.strip() else indent + 4
                        if sub_line.strip() == '':
                            sub_lines.append(sub_line)
                            j += 1
                            continue
                        if sub_leading <= indent:
                            break
                        sub_lines.append(sub_line)
                        j += 1
                    items.append({k: _parse_yaml_block('\n'.join(sub_lines), indent + 4)})
                    i = j
                    continue
                else:
                    items.append({k: _parse_scalar(v)})
            else:
                items.append(_parse_scalar(val))
        i += 1
    return items


def _parse_scalar(val):
    if not val:
        return ''
    if (val.startswith("'") and val.endswith("'")) or \
       (val.startswith('"') and val.endswith('"')):
        return val[1:-1]
    if re.match(r'^\d{4}-\d{2}-\d{2}$', val):
        return val
    if val.lower() == 'true':
        return True
    if val.lower() == 'false':
        return False
    try:
        return int(val)
    except ValueError:
        pass
    return val


# ---------------------------------------------------------------------------
# Unified frontmatter parser — picks PyYAML or fallback automatically
# ---------------------------------------------------------------------------

def parse_frontmatter(text):
    """
    Extract and parse YAML frontmatter from an MD file.
    Returns (fields_dict, body_text).
    Uses PyYAML when available, otherwise the built-in fallback parser.
    """
    m = re.match(r'^---\r?\n(.*?)\n---\r?\n?', text, re.DOTALL)
    if not m:
        return {}, text
    raw_yaml = m.group(1)
    body = text[m.end():]

    if USING_PYYAML:
        fields = _load_yaml(raw_yaml)
        # PyYAML converts dates to date objects — normalise to strings
        fields = _normalise_pyyaml_dates(fields)
    else:
        fields = _parse_yaml_block(raw_yaml, indent=0)

    return fields, body


def _normalise_pyyaml_dates(obj):
    """Recursively convert date/datetime objects from PyYAML to Y-m-d strings."""
    if isinstance(obj, dict):
        return {k: _normalise_pyyaml_dates(v) for k, v in obj.items()}
    if isinstance(obj, list):
        return [_normalise_pyyaml_dates(i) for i in obj]
    if isinstance(obj, (date, datetime)):
        return obj.strftime('%Y-%m-%d')
    return obj


# ---------------------------------------------------------------------------
# Serialiser — always used (controls output style regardless of parse path)
# ---------------------------------------------------------------------------

def format_date(val):
    """Ensure dates are output as plain Y-m-d strings."""
    if isinstance(val, (date, datetime)):
        return val.strftime('%Y-%m-%d')
    if isinstance(val, str) and re.match(r'^\d{4}-\d{2}-\d{2}', val):
        return val[:10]
    return val


def yaml_scalar(val):
    """Serialise a scalar value. Strings with special chars get single-quoted."""
    if isinstance(val, bool):
        return 'true' if val else 'false'
    if isinstance(val, int):
        return str(val)
    if isinstance(val, (date, datetime)):
        return format_date(val)
    s = str(val)
    needs_quotes = (
        ':' in s or
        '#' in s or
        "'" in s or
        ' ' in s or
        s.startswith('{') or
        s.startswith('[') or
        s.startswith('*') or
        s.startswith('&') or
        s[:1].isupper() or
        s.lower() in ('true', 'false', 'null', 'yes', 'no') or
        re.match(r'^\d{4}-\d{2}-\d{2}', s) or
        (s != s.strip()) or
        s == ''
    )
    if needs_quotes:
        escaped = s.replace("'", "''")
        return f"'{escaped}'"
    return s


def serialise_value(val, indent):
    """Recursively serialise a value at the given indent level."""
    pad = ' ' * indent
    child_pad = ' ' * (indent + 2)
    if isinstance(val, list):
        lines = []
        for item in val:
            if isinstance(item, dict):
                first = True
                for k, v in item.items():
                    if isinstance(v, (dict, list)):
                        if first:
                            lines.append(f"{pad}- {k}:")
                            first = False
                        else:
                            lines.append(f"{child_pad}{k}:")
                        lines.append(serialise_value(v, indent + 4))
                    else:
                        if first:
                            lines.append(f"{pad}- {k}: {yaml_scalar(v)}")
                            first = False
                        else:
                            lines.append(f"{child_pad}{k}: {yaml_scalar(v)}")
            else:
                lines.append(f"{pad}- {yaml_scalar(item)}")
        return '\n'.join(lines)
    elif isinstance(val, dict):
        lines = []
        for k, v in val.items():
            if isinstance(v, (dict, list)):
                lines.append(f"{pad}{k}:")
                lines.append(serialise_value(v, indent + 2))
            else:
                lines.append(f"{pad}{k}: {yaml_scalar(v)}")
        return '\n'.join(lines)
    else:
        return f"{pad}{yaml_scalar(val)}"


def build_frontmatter(header_fields, sub_items_sorted):
    """Reconstruct the full frontmatter block."""
    lines = ['---']
    for key, val in header_fields.items():
        if key == 'sub-items':
            continue
        if key == 'sub-indexed':
            lines.append(f"sub-indexed: {date.today().strftime('%Y-%m-%d')}")
            continue
        if isinstance(val, (dict, list)):
            lines.append(f"{key}:")
            lines.append(serialise_value(val, indent=2))
        else:
            lines.append(f"{key}: {yaml_scalar(val)}")

    lines.append('sub-items:')
    for slug, fields in sub_items_sorted:
        lines.append(f"  - {slug}:")
        for fkey, fval in fields.items():
            if isinstance(fval, (dict, list)):
                lines.append(f"      {fkey}:")
                lines.append(serialise_value(fval, indent=8))
            else:
                v = format_date(fval) if re.match(r'date', fkey, re.IGNORECASE) else fval
                lines.append(f"      {fkey}: {yaml_scalar(v)}")

    lines.append('---')
    return '\n'.join(lines) + '\n'


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main():
    if len(sys.argv) < 2:
        print("Usage: python index_md.py <directory>")
        sys.exit(1)

    directory = sys.argv[1]
    index_path = os.path.join(directory, '_index.md')

    if not os.path.isfile(index_path):
        print(f"Error: _index.md not found in {directory}")
        sys.exit(1)

    parser_name = "PyYAML" if USING_PYYAML else "built-in fallback parser (~3 levels)"
    print(f"Parser: {parser_name}")

    # --- Read and parse _index.md ---
    with open(index_path, 'r', encoding='utf-8') as f:
        index_text = f.read()

    index_fields, index_body = parse_frontmatter(index_text)

    sub_type = index_fields.get('sub-type', '')

    if not sub_type:
        print("Warning: no 'sub-type' found in _index.md — nothing to filter on.")

    # --- Scan MD files ---
    sub_items = []

    for filename in os.listdir(directory):
        if not filename.endswith('.md'):
            continue
        if filename == '_index.md':
            continue

        filepath = os.path.join(directory, filename)
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        fields, _ = parse_frontmatter(content)
        if not fields:
            continue

        if fields.get('type', '') != sub_type:
            continue

        slug = filename.split('.')[0]

        entry = {}
        for k, v in fields.items():
            if k == 'type':
                continue
            if k == 'date' or (isinstance(v, str) and re.match(r'^\d{4}-\d{2}-\d{2}', str(v))):
                entry[k] = format_date(v)
            else:
                entry[k] = v

        sub_items.append((slug, entry))

    print(f"Found {len(sub_items)} file(s) with type='{sub_type}'")

    # --- Build and write new _index.md ---
    new_frontmatter = build_frontmatter(index_fields, sub_items)
    new_content = new_frontmatter + index_body

    with open(index_path, 'w', encoding='utf-8') as f:
        f.write(new_content)

    print(f"Written: {index_path}")


if __name__ == '__main__':
    main()
