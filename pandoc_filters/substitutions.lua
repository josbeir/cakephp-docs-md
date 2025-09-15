-- Substitutions filter: Replace Sphinx substitutions with actual values
-- Handles |phpversion|, |minphpversion| and other substitutions

function Str(elem)
    local text = elem.text
    
    -- Replace common CakePHP substitutions
    text = text:gsub("|phpversion|", os.getenv("PHPVERSION") or "8.4")
    text = text:gsub("|minphpversion|", os.getenv("MINPHPVERSION") or "8.1")
    
    -- Return modified string if changes were made
    if text ~= elem.text then
        return pandoc.Str(text)
    end
    
    return elem
end

-- Handle substitutions in raw blocks before pandoc processes them
function RawBlock(elem)
    if elem.format == "rst" then
        local text = elem.text
        text = text:gsub("|phpversion|", os.getenv("PHPVERSION") or "8.4")
        text = text:gsub("|minphpversion|", os.getenv("MINPHPVERSION") or "8.1")
        
        if text ~= elem.text then
            return pandoc.RawBlock(elem.format, text)
        end
    end
    return elem
end

function Code(elem)
    local text = elem.text
    
    -- Replace substitutions in code blocks too
    text = text:gsub("|phpversion|", os.getenv("PHPVERSION") or "8.4")
    text = text:gsub("|minphpversion|", os.getenv("MINPHPVERSION") or "8.1")
    
    if text ~= elem.text then
        return pandoc.Code(text, elem.attr)
    end
    
    return elem
end

function CodeBlock(elem)
    local text = elem.text
    
    -- Replace substitutions in code blocks
    text = text:gsub("|phpversion|", os.getenv("PHPVERSION") or "8.4")
    text = text:gsub("|minphpversion|", os.getenv("MINPHPVERSION") or "8.1")
    
    if text ~= elem.text then
        return pandoc.CodeBlock(text, elem.attr)
    end
    
    return elem
end