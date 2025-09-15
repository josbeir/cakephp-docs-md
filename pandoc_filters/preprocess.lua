-- Pre-process filter: Handle RST content before pandoc processes it
-- This runs early to fix substitutions and other RST-specific issues

function RawBlock(elem)
    if elem.format == "rst" then
        local text = elem.text
        
        -- Handle substitutions in RST content
        text = text:gsub("|phpversion|", os.getenv("PHPVERSION") or "8.4")
        text = text:gsub("|minphpversion|", os.getenv("MINPHPVERSION") or "8.1")
        
        return pandoc.RawBlock(elem.format, text)
    end
    return elem
end

-- Process the entire document to handle global substitutions
function Pandoc(doc)
    -- Convert the document to string, do global replacements, then parse back
    local content = pandoc.write(doc, "rst")
    
    -- Replace substitutions
    content = content:gsub("|phpversion|", os.getenv("PHPVERSION") or "8.4")
    content = content:gsub("|minphpversion|", os.getenv("MINPHPVERSION") or "8.1")
    
    -- Parse back and return
    return pandoc.read(content, "rst")
end