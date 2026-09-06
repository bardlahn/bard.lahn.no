# bard.lahn.no

### My personal website
Redesigning my website in 2026, I ended up writing a lightweight CMS from scratch, mostly as an exercise but also because I could not find anything that really suited my needs. This is the result, which will also be live on [bard.lahn.no](https://bard.lahn.no) from mid-May.

All content is contained in markdown files and rendered by PHP. New pages can be set up by adding folders with an 'index.en.md' and/or 'index.no.md' file. Blog posts or sub-elements under a page can be added as md files in a subdirectory '_sub' and indexed in a helper file '_index.md'.

The system is not well documented at the moment, and probably never will be - but feel free to have a look around.

### To do:

- Move listing of alternative languages from PHP include file to variable function in md-render.php
- Move inclusion of sub-emelent listing from PHP include file to include-block in md-render.php
- Implement "trusted" switch in config
- Disallow file inclusion from include path
- Serve static files through index.php, to disallow direct access to static files
- Collect path variables in array $sitePaths?