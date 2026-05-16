import os
import re
from datetime import datetime, timezone

directory = "."

def convert_date(content):
    match = re.match(r'^(---\n)(.*?)(---\n?)', content, re.DOTALL)
    if not match:
        return content, False

    front = match.group(2)

    # Match both top-level and indented date fields with Unix timestamps
    new_front, count = re.subn(
        r'^(\s*\w*date\w*:\s*)(\d{9,11})\s*$',
        lambda m: m.group(1) + datetime.fromtimestamp(int(m.group(2)), tz=timezone.utc).strftime('%Y-%m-%d'),
        front,
        flags=re.MULTILINE | re.IGNORECASE
    )

    if count == 0:
        return content, False

    new_content = match.group(1) + new_front + match.group(3) + content[match.end():]
    return new_content, True

for root, dirs, files in os.walk(directory):
    for filename in files:
        if filename.endswith(".md"):
            filepath = os.path.join(root, filename)
            with open(filepath, 'r', encoding='utf-8') as f:
                original = f.read()

            converted, changed = convert_date(original)

            if changed:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(converted)
                print(f"Updated: {filepath}")
            else:
                print(f"Skipped: {filepath}")