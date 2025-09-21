# CakePHP Documentation Conversion Project Agent Guidelines

## Project Overview
This project converts CakePHP's RST documentation to Markdown using Pandoc with custom Lua filters. The converted docs are available at: **[CakePHP (new) book](https://newbook.cakephp.org/)**

## Main Conversion Commands
- **Convert single directory**: `./convert <input_dir> <output_dir>`
  - Example: `./convert legacy/en docs/5/en`
- **Convert all branches**: `./convert_branches` (fetches and converts 5.x, 4.x, 3.x, 2.x branches)
- **Test single file**: Use environment variables with pandoc directly (see convert script)

## GitHub Actions
- **Automated conversion**: `.github/workflows/convert-docs.yml`
- **Triggers**: Daily at 2 AM UTC, or manual dispatch
- **Process**: Fetches latest docs, converts, commits changes if any

## Dependencies
- **Pandoc 3.x** with Lua 5.4 support
- **Python 3** (for RST preprocessing)
- **Standard Unix tools**: bash, sed, awk, find, mktemp
- **Git** (for branch fetching and commits)

## Project Structure
- **Source docs**: Fetched from `https://github.com/cakephp/docs.git` branches
- **Legacy source**: `legacy/{lang}/` (RST format, if present locally)
- **Converted output**: `docs/{version}/{lang}/` (Markdown format)
- **Conversion script**: `convert` (main converter)
- **Branch converter**: `convert_branches` (multi-branch automation)
- **Pandoc filters**: `pandoc_filters/*.lua` (custom conversion logic)

## Conversion Pipeline
1. **Preprocessing**: Python script fixes include paths, handles PHP directives
2. **AWK processing**: Formats PHP namespaces, classes, methods
3. **Pandoc conversion**: RST → GFM with Lua filters
4. **Post-processing**: Restores version variables

## Key Pandoc Filters
- `meta.lua` - Handles frontmatter and metadata
- `doc_links.lua` - Converts RST cross-references
- `ref.lua` - Handles Sphinx references
- `php_domain.lua` - Processes PHP-specific directives
- `codeblocks.lua` - Improves code block detection and language assignment
- `toctree.lua` - Converts Sphinx toctrees to navigation
- `versionadded.lua` - Handles version directive blocks
- `containers.lua` - Processes note/warning/tip containers
- `images.lua` - Handles image references and paths
- `table_cleanup.lua` - Improves table formatting

## Environment Variables (for filters)
- `DESTINATION_CONTEXT` - Target markdown file path
- `DESTINATION_FOLDER` - Output directory root
- `SOURCE_FOLDER` - Input directory root
- `CURRENT_SOURCE_FILE` - Current RST file being processed

## Content Guidelines
- **Input format**: reStructuredText (RST) with Sphinx
- **Output format**: GitHub Flavored Markdown (GFM)
- **Code standards**: PSR-12 for PHP examples, 4-space indentation
- **Conversion quality**: Preserves semantic structure, cross-references, code blocks
- **Multi-language**: Supports EN, JA languages across multiple CakePHP versions