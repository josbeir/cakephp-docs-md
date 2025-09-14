# AGENTS.md

## Build & Preview Commands
- Start local docs server: `npm run docs:dev`
- Build static site: `npm run docs:build`
- Build for local static output: `npm run docs:buildLocal`
- Preview built site: `npm run docs:preview`
- **No test or lint scripts are defined.**

## Code Style Guidelines
- **JavaScript:**
  - Use ES module imports (`import ... from ...`).
  - Prefer single quotes, 2-space indentation, and trailing commas where possible.
  - Use descriptive, camelCase for variables/functions; PascalCase for classes.
  - Handle errors with try/catch and clear error messages.
  - Keep lines ≤ 100 chars when possible.
  - Comment code clearly in English.
- **PHP in docs/examples:**
  - Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) and [CakePHP Coding Conventions](docs/en/contributing/cakephp-coding-conventions.md).
  - Four-space indentation, camelBack for functions/variables, CamelCase for classes.
  - Always use curly braces for control structures.
  - Add tests for new features (if relevant).

## Documentation Conversion Tool
- `bin/convert.php` is a CLI tool to convert CakePHP’s RST docs to Markdown.
- Usage: `php bin/convert.php [input_dir] [output_dir]`
- Handles Sphinx/CakePHP RST features: headings, code blocks, admonitions, PHP/class/method directives, cross-references, images, lists, tables, and more.
- Preserves custom directives and converts them to Markdown/GitHub alert syntax.
- Focuses on the `/en` folder by default, but can process any RST directory.
- Copies static assets (`_static`) to the output directory.
- Implements robust error handling and outputs conversion progress.
- Run with `-h` or `--help` for full usage details.

## General
- No Cursor or Copilot rules are present.
- See `docs/en/contributing/code.md` and `docs/en/contributing/cakephp-coding-conventions.md` for more details on PHP code style.
