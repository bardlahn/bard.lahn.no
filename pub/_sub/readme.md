# Overview of publication types and frontmatter data

## Fields in main frontmatter section

The following fields are used for general information about the publication:

- title
- date
- abstract
- language
- tags
- authors
  - subelement:
    - name
    - [url]
    - [orcid]
- routes:
  - external
- type: publication

The field 'authors' can have multiple subelements. If 'self' is given as a subelement (or as the only content of the authors field), author data will be fetched using _fetch-config_. Other authors will need an element name (slug), and the field 'name' and either 'url' or 'orcid' given.

Note that if an URL is given in 'routes/external', element listings will link to that URL instead of the element itself.

## Publication-specific fields: 'pub-data'

If the main field 'type' is set to 'publication', the following fields nested under the top-level field 'pub-data' can be:

- pub-type (can be either book, article, thesis, report, or chapter)
- pub-lang (language of publication, can be different from page language)
- doi (applies to all pub-types)
- issn (applies to all pub-types)
- isbn (applies to pub-types book, report, chapter)
- file (path for downloadable file, according to _action=download_ logic. Applies to all pub-types)
- publisher (applies to pub-types book, thesis, report, chapter)
- journal (applies to pub-type article)
- volume (applies to pub-type article)
- issue (applies to pub-type article)
- pages (applies to pub-types article, chapter)
- number (applies to pub-type report)
- book (applies to pub-type chapter)
- editors (follows same logic as 'authors' field, applies to pub-type chapter)
- degree (applies to pub-type thesis)