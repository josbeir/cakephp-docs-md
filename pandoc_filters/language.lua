-- Language filter: Normalize code block language identifiers for markdown compatibility
-- Converts non-standard language identifiers to standard ones

function CodeBlock(elem)
    local lang = elem.attr.classes[1] or ""
    local original_lang = lang
    
    -- Language mappings for markdown compatibility
    local language_map = {
        ["mysql"] = "sql",           -- MySQL syntax is SQL
        ["postgresql"] = "sql",      -- PostgreSQL syntax is SQL  
        ["sqlite"] = "sql",          -- SQLite syntax is SQL
        ["SQL"] = "sql",             -- Normalize uppercase SQL
        ["console"] = "bash",        -- Console commands are typically bash
        ["shell"] = "bash",          -- Shell commands are bash
        ["apacheconf"] = "apache",   -- Apache config is commonly called 'apache'
        ["js"] = "javascript",       -- Normalize js to javascript
        ["yml"] = "yaml",            -- Normalize yml to yaml
        ["htm"] = "html",            -- Normalize htm to html
        ["jsonc"] = "json",          -- JSON with comments is still JSON for highlighting
    }
    
    -- Apply language mapping if exists
    if language_map[lang] then
        lang = language_map[lang]
    end
    
    -- Normalize language to lowercase (except for special cases)
    local special_cases = {
        ["JavaScript"] = "javascript",
        ["TypeScript"] = "typescript",
        ["HTML"] = "html",
        ["CSS"] = "css",
        ["XML"] = "xml",
        ["JSON"] = "json",
        ["YAML"] = "yaml",
        ["SQL"] = "sql",
        ["PHP"] = "php",
    }
    
    if special_cases[lang] then
        lang = special_cases[lang]
    else
        lang = lang:lower()
    end
    
    -- Update the language if it changed
    if lang ~= original_lang then
        elem.attr.classes[1] = lang
        return elem
    end
    
    return elem
end

-- Also handle inline code that might have language attributes
function Code(elem)
    -- This handles inline code, but inline code doesn't typically have language attributes
    -- in markdown, so we'll just return as-is
    return elem
end