# CakePHP Documentation Project Agent Guidelines

## Build Commands
- **Build HTML docs**: `make html` (all languages) or `make html-en` (English only)
- **Build EPUB**: `make epub` or `make epub-en`
- **Build PDF**: `make latex-en` then `make pdf-en`
- **Clean builds**: `make clean`
- **Single language build**: Navigate to language dir (e.g., `en/`) and run `make html`

## Dependencies
- Python 3 with pip
- Install requirements: `pip install -r requirements.txt`
- For PDF: LaTeX package required

## Project Structure
- **Documentation source**: `legacy/{lang}/` (RST format)
- **Config**: `legacy/config/conf.py` (Sphinx configuration)
- **Build output**: `build/` directory
- **Experimental Markdown**: `convert_all.sh` converts RST to Markdown in `markdown/en/`

## Content Guidelines
- Format: reStructuredText (RST) with Sphinx
- Follow PSR-12 coding standards for PHP examples
- Use 4 spaces for indentation in code examples
- Include `.. todo::` for outstanding issues
- Use proper Sphinx directives for code blocks, notes, warnings
- Keep documentation practical and example-driven