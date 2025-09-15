-- Links filter: Convert Sphinx cross-references to Markdown links
-- Handles :doc:, :ref:, and other Sphinx link formats

function Link(link)
    local url = link.target
    local text = pandoc.utils.stringify(link.content)
    
    -- Convert .html links to .md
    if url:match("%.html$") then
        url = url:gsub("%.html$", ".md")
        return pandoc.Link(link.content, url, link.title)
    end
    
    return link
end

function Code(elem)
    local text = elem.text
    local original_text = text
    
    -- Handle Sphinx interpreted-text roles that pandoc converts to Code elements
    -- Check if this code element has the interpreted-text class
    local has_interpreted_text = false
    if elem.classes then
        for i, class in ipairs(elem.classes) do
            if class == "interpreted-text" then
                has_interpreted_text = true
                break
            end
        end
    end
    
    if has_interpreted_text then
        local role = elem.attributes and elem.attributes.role
        
        if role == "ref" then
            local link_text = text
            local link_url = "#" .. text:lower():gsub("%s+", "-")
            
            -- Extract custom link text if present: "Link Text <anchor>"
            local custom_text, anchor = text:match("^(.-)%s+<%s*(.-)%s*>$")
            if custom_text and anchor then
                link_text = custom_text
                -- For ref links, convert anchor to local anchor link
                link_url = "#" .. anchor:lower():gsub("%s+", "-")
            end
            
            -- Escape angle brackets in link text for proper Markdown rendering
            link_text = link_text:gsub("<", "&lt;"):gsub(">", "&gt;")
            -- Clean up angle brackets in URL (convert to hyphens)
            link_url = link_url:gsub("[<>]", "-"):gsub("%-+", "-"):gsub("%-$", "")
            
            return pandoc.RawInline("markdown", "[" .. link_text .. "](" .. link_url .. ")")
        elseif role == "doc" then
            local link_text = text
            local link_url = text
            
            -- Extract custom link text if present
            local custom_text, path = text:match("^(.-)%s+<%s*(.-)%s*>$")
            if custom_text and path then
                link_text = custom_text
                link_url = path
            end
            
            link_url = link_url:gsub("^/", "")
            link_url = link_url .. ".md"
            
            return pandoc.RawInline("markdown", "[" .. link_text .. "](" .. link_url .. ")")
        end
    end
    
    -- Handle :doc: cross-references
    text = text:gsub(":doc:`([^`]+)`", function(match)
        local link_text = match
        local link_url = match
        
        -- Extract custom link text if present
        local custom_text, path = match:match("^(.-)%s+<%s*(.-)%s*>$")
        if custom_text and path then
            link_text = custom_text
            link_url = path
        end
        
        link_url = link_url:gsub("^/", "")
        link_url = link_url .. ".md"
        
        return "[" .. link_text .. "](" .. link_url .. ")"
    end)
    
    -- Handle :ref: cross-references
    text = text:gsub(":ref:`([^`]+)`", function(match)
        local link_text = match
        local link_url = "#" .. match:lower():gsub("%s+", "-")
        
        -- Extract custom link text if present
        local custom_text, anchor = match:match("^(.-)%s+<%s*(.-)%s*>$")
        if custom_text and anchor then
            link_text = custom_text
            link_url = "#" .. anchor:lower():gsub("%s+", "-")
        end
        
        -- Escape angle brackets in link text for proper Markdown rendering
        link_text = link_text:gsub("<", "&lt;"):gsub(">", "&gt;")
        
        return "[" .. link_text .. "](" .. link_url .. ")"
    end)
    
    -- Normalize PHP FQDN by converting double backslashes to single backslashes
    -- This handles code blocks that come from :php:meth:, :php:class:, etc.
    if text:match("^[~]?[A-Z][A-Za-z0-9_]*\\") then
        -- This looks like a PHP FQDN (starts with optional ~, then uppercase letter, contains backslashes)
        local normalized_text = text:gsub("\\\\", "\\")
        if normalized_text ~= text then
            return pandoc.Code(normalized_text)
        end
    end
    
    -- If we made changes to doc/ref links, return the modified text
    if text ~= original_text then
        return pandoc.RawInline("markdown", text)
    end
    
    return elem
end

function Span(elem)
    -- Handle Sphinx interpreted-text roles that pandoc converts
    -- e.g., `RoutingMiddleware <routing-middleware>`{.interpreted-text role="ref"}
    
    -- Check if this span has the interpreted-text class
    local has_interpreted_text = false
    if elem.classes then
        for i, class in ipairs(elem.classes) do
            if class == "interpreted-text" then
                has_interpreted_text = true
                break
            end
        end
    end
    
    if has_interpreted_text then
        local role = elem.attributes and elem.attributes.role
        local text = pandoc.utils.stringify(elem.content)
        
        if role == "ref" then
            local link_text = text
            local link_url = "#" .. text:lower():gsub("%s+", "-")
            
            -- Extract custom link text if present: "Link Text <anchor>"
            local custom_text, anchor = text:match("^(.-)%s+<%s*(.-)%s*>$")
            if custom_text and anchor then
                link_text = custom_text
                -- For ref links, convert anchor to local anchor link
                link_url = "#" .. anchor:lower():gsub("%s+", "-")
            end
            
            -- Escape angle brackets in link text for proper Markdown rendering
            link_text = link_text:gsub("<", "&lt;"):gsub(">", "&gt;")
            -- Clean up angle brackets in URL (convert to hyphens)
            link_url = link_url:gsub("[<>]", "-"):gsub("%-+", "-"):gsub("%-$", "")
            
            return pandoc.Link(link_text, link_url)
        elseif role == "doc" then
            local link_text = text
            local link_url = text
            
            -- Extract custom link text if present
            local custom_text, path = text:match("^(.-)%s+<%s*(.-)%s*>$")
            if custom_text and path then
                link_text = custom_text
                link_url = path
            end
            
            link_url = link_url:gsub("^/", "")
            link_url = link_url .. ".md"
            
            return pandoc.Link(link_text, link_url)
        end
    end
    
    return elem
end

function Str(elem)
    local text = elem.text
    
    -- Handle inline :doc: references that might not be in code
    text = text:gsub(":doc:`([^`]+)`", function(match)
        local link_text = match
        local link_url = match
        
        -- Extract custom link text if present
        local custom_text, path = match:match("^(.-)%s+<%s*(.-)%s*>$")
        if custom_text and path then
            link_text = custom_text
            link_url = path
        end
        
        link_url = link_url:gsub("^/", "")
        link_url = link_url .. ".md"
        
        return "[" .. link_text .. "](" .. link_url .. ")"
    end)
    
    -- Handle inline :ref: references
    text = text:gsub(":ref:`([^`]+)`", function(match)
        local link_text = match
        local link_url = "#" .. match:lower():gsub("%s+", "-")
        
        -- Extract custom link text if present
        local custom_text, anchor = match:match("^(.-)%s+<%s*(.-)%s*>$")
        if custom_text and anchor then
            link_text = custom_text
            link_url = "#" .. anchor:lower():gsub("%s+", "-")
        end
        
        -- Escape angle brackets in link text for proper Markdown rendering
        link_text = link_text:gsub("<", "&lt;"):gsub(">", "&gt;")
        -- Clean up angle brackets in URL (convert to hyphens)
        link_url = link_url:gsub("[<>]", "-"):gsub("%-+", "-"):gsub("%-$", "")
        
        return "[" .. link_text .. "](" .. link_url .. ")"
    end)
    
    if text ~= elem.text then
        return pandoc.Str(text)
    end
    
    return elem
end