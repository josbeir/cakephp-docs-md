-- Indented code block conversion filter
-- Converts indented code blocks to fenced code blocks with language detection

local function detect_language(code_text)
    -- Remove leading/trailing whitespace for analysis
    local trimmed = code_text:gsub("^%s+", ""):gsub("%s+$", "")
    
    -- PHP detection (order matters - most specific first)
    -- CakePHP-specific patterns (highest priority)
    if trimmed:match("App::") or                    -- App:: static calls
       trimmed:match("Cake\\") or                   -- CakePHP namespace
       trimmed:match("use%s+Cake\\") or             -- CakePHP use statements
       trimmed:match("new%s+Cake\\") or             -- CakePHP class instantiation
       trimmed:match("extends%s+Cake\\") or         -- Extending CakePHP classes
       trimmed:match("implements%s+Cake\\") or      -- Implementing CakePHP interfaces
       -- Router and other CakePHP class static calls
       trimmed:match("Router::") or
       trimmed:match("Configure::") or
       trimmed:match("Cache::") or
       trimmed:match("Log::") or
       trimmed:match("Hash::") or
       trimmed:match("Plugin::") or
       -- CakePHP utility classes
       trimmed:match("Inflector::") or
       trimmed:match("Number::") or
       trimmed:match("Text::") or
       trimmed:match("Time::") or
       trimmed:match("Security::") or
       trimmed:match("Xml::") or
       trimmed:match("Collection::") or
       -- Database and ORM classes
       trimmed:match("TableRegistry::") or
       trimmed:match("Query::") or
       -- Event system
       trimmed:match("EventManager::") or
       -- I18n classes
       trimmed:match("I18n::") or
       trimmed:match("FrozenTime::") or
       trimmed:match("FrozenDate::") then
        return "php"
    end

    -- General PHP patterns
    if trimmed:match("^<%?php") or
       trimmed:match("^<%?=") or
       trimmed:match("^<%?") or
       trimmed:match("%$this%->") or
       trimmed:match("%$[%w_]+%s*=") or
       trimmed:match("use%s+[%w\\]+;") or
       trimmed:match("namespace%s+[%w\\]+") or
       trimmed:match("->%w+%(") or
       trimmed:match("^class%s+%w+") or
       trimmed:match("\nclass%s+%w+") or  -- class at beginning of any line
       trimmed:match("^public%s+function") or
       trimmed:match("\npublic%s+function") or  -- public function at beginning of any line
       trimmed:match("^private%s+function") or
       trimmed:match("\nprivate%s+function") or  -- private function at beginning of any line
       trimmed:match("^protected%s+function") or
       trimmed:match("\nprotected%s+function") or  -- protected function at beginning of any line
       (trimmed:match("^function%s+%w+") and (trimmed:match("%$") or trimmed:match("->"))) or  -- PHP function only if contains PHP syntax
       (trimmed:match("\nfunction%s+%w+") and (trimmed:match("%$") or trimmed:match("->"))) or  -- function at beginning of any line with PHP syntax
       -- PHP array detection
       (trimmed:match("^%s*%[") and (trimmed:match("'[^']*'%s*=>") or trimmed:match('"[^"]*"%s*=>') or trimmed:match('%$[%w_]+'))) or  -- array with string keys or variables
       (trimmed:match("^%s*%[") and trimmed:match("object%(")) or  -- array containing objects
       (trimmed:match("=>%s*object%(")) then  -- object assignment
        return "php"
    end
    
    -- HTML detection
    if trimmed:match("^%s*<%w+") or
       trimmed:match("^%s*</%w+>") or
       trimmed:match("^%s*<!DOCTYPE") or
       trimmed:match("^%s*<html") or
       trimmed:match("^%s*<div") or
       trimmed:match("^%s*<span") or
       trimmed:match("^%s*<meta") or
       trimmed:match("^%s*<link") or
       trimmed:match("^%s*<form") or
       trimmed:match("^%s*<input") or
       trimmed:match("^%s*<script") or
       trimmed:match("^%s*<style") then
        return "html"
    end
    
    -- CSS detection
    if trimmed:match("^%s*[%.#]?[%w%-]+%s*{") or
       trimmed:match("^%s*[%w%-]+:%s*[^;]+;") or
       trimmed:match("@media") or
       trimmed:match("@import") then
        return "css"
    end
    
    -- JavaScript detection
    if trimmed:match("^%s*var%s+%w+") or
       trimmed:match("^%s*let%s+%w+") or
       trimmed:match("^%s*const%s+%w+") or
       trimmed:match("^%s*function%s*%w*%s*%(") or
       trimmed:match("=>%s*{") or
       trimmed:match("^%s*import%s+") or
       trimmed:match("^%s*export%s+") or
       trimmed:match("console%.log") or
       trimmed:match("document%.") or
       trimmed:match("window%.") then
        return "javascript"
    end
    
    -- JSON detection
    if (trimmed:match("^%s*{") and trimmed:match("}%s*$")) or
       (trimmed:match("^%s*%[") and trimmed:match("%]%s*$")) or
       trimmed:match('^%s*"[^"]+"%s*:%s*') then
        -- Check if it looks like JSON structure
        if trimmed:match('"[^"]+"%s*:%s*"[^"]*"') or
           trimmed:match('"[^"]+"%s*:%s*%d+') or
           trimmed:match('"[^"]+"%s*:%s*%[') or
           trimmed:match('"[^"]+"%s*:%s*{') then
            return "json"
        end
    end
    
    -- SQL detection
    if trimmed:match("^%s*SELECT%s+") or
       trimmed:match("^%s*INSERT%s+") or
       trimmed:match("^%s*UPDATE%s+") or
       trimmed:match("^%s*DELETE%s+") or
       trimmed:match("^%s*CREATE%s+") or
       trimmed:match("^%s*ALTER%s+") or
       trimmed:match("^%s*DROP%s+") or
       trimmed:match("FROM%s+%w+") or
       trimmed:match("WHERE%s+") then
        return "sql"
    end
    
    -- Shell/Bash detection
    if trimmed:match("^%s*#!/bin/bash") or
       trimmed:match("^%s*#!/bin/sh") or
       trimmed:match("^%s*%$%s+") or
       trimmed:match("^%s*sudo%s+") or
       trimmed:match("^%s*cd%s+") or
       trimmed:match("^%s*ls%s+") or
       trimmed:match("^%s*mkdir%s+") or
       trimmed:match("^%s*rm%s+") or
       trimmed:match("^%s*cp%s+") or
       trimmed:match("^%s*mv%s+") then
        return "bash"
    end
    
    -- YAML detection
    if trimmed:match("^%s*%w+:%s*$") or
       trimmed:match("^%s*%w+:%s+[^%s]") or
       trimmed:match("^%s*%-%s+") then
        return "yaml"
    end
    
    -- XML detection (after HTML check)
    if trimmed:match("^%s*<%?xml") or
       trimmed:match("^%s*<%w+:%w+") then
        return "xml"
    end
    
    -- Apache config detection
    if trimmed:match("^%s*<%w+") and 
       (trimmed:match("Directory") or trimmed:match("VirtualHost") or trimmed:match("Location")) then
        return "apache"
    end
    
    -- If no language detected, return nil
    return nil
end

local function is_likely_code(text)
    -- Check if this looks like code vs regular indented text
    local trimmed = text:gsub("^%s+", ""):gsub("%s+$", "")
    
    -- Skip empty or very short lines
    if #trimmed < 3 then
        return false
    end
    
    -- Skip lines that look like regular prose
    if trimmed:match("^[A-Z][^%.]*%.$") or  -- Sentences ending with period
       trimmed:match("^[A-Z][^%?]*%?$") or  -- Questions
       trimmed:match("^[A-Z][^%!]*%!$") or  -- Exclamations
       trimmed:match("^Note:") or
       trimmed:match("^Warning:") or
       trimmed:match("^Important:") or
       trimmed:match("^Example:") then
        return false
    end
    
    -- Code indicators
    if trimmed:match("[{}();]") or          -- Common code punctuation
       trimmed:match("^[%w_]+%s*=") or      -- Variable assignments
       trimmed:match("->") or               -- Method calls
       trimmed:match("::") or               -- Static calls
       trimmed:match("<%w+") or             -- HTML/XML tags
       trimmed:match("^<%?") or             -- PHP tags (including <?= and <?php)
       trimmed:match("^[%$#]") or           -- Shell variables or comments
       trimmed:match("^//") or              -- Comments
       trimmed:match("^/%*") or             -- Block comments
       trimmed:match("%w+%(.*%)") or        -- Function calls
       -- Array/object patterns
       (trimmed:match("^%s*%[") and trimmed:match("%]%s*$")) or  -- Starts with [ and ends with ]
       trimmed:match("'[^']*'%s*=>") or     -- String keys with =>
       trimmed:match('"[^"]*"%s*=>') or     -- Double-quoted string keys with =>
       trimmed:match("=>") then             -- Any => operator (common in PHP, also in JS object literals)
        return true
    end
    
    return false
end

function CodeBlock(elem)
    -- Only process indented code blocks (those without explicit language)
    if elem.classes and #elem.classes > 0 then
        return elem -- Already has a language, leave it alone
    end
    
    -- Check if this looks like actual code
    if not is_likely_code(elem.text) then
        return elem -- Not code, leave as is
    end
    
    -- Detect language
    local language = detect_language(elem.text)

    -- Create new code block with detected language, or fallback to generic code block
    if language then
        elem.classes = {language}
    else
        -- Fallback: if it looks like code but we can't detect the language,
        -- still make it a fenced code block (this forces fenced output in GFM)
        elem.classes = {"text"}  -- Generic fallback
    end

    return elem
end