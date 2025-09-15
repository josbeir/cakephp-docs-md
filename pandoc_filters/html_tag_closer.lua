-- VitePress Compatibility filter: Ensures HTML tags are VitePress-compatible
-- 
-- USAGE:
-- Apply to conversion: --lua-filter=pandoc_filters/html_tag_closer.lua
--
-- PURPOSE:
-- VitePress can have issues with certain HTML patterns. This filter ensures:
-- 1. Self-closing tags use proper syntax (< /> not just <)
-- 2. Common problematic tags are handled correctly
-- 3. Inline HTML is preserved properly

-- List of self-closing tags that should use /> syntax
local self_closing_tags = {
    "area", "base", "br", "col", "embed", "hr", "img", "input", 
    "link", "meta", "param", "source", "track", "wbr"
}

-- Convert to set for faster lookup
local self_closing_set = {}
for _, tag in ipairs(self_closing_tags) do
    self_closing_set[tag:lower()] = true
end

-- Function to fix self-closing tag syntax
local function fix_self_closing_tags(text)
    if not text then return text end
    
    local result = text
    
    -- Fix self-closing tags that don't have proper /> syntax
    for tag in pairs(self_closing_set) do
        -- Pattern: <tag ...> to <tag ... />
        result = result:gsub("<(" .. tag .. "[^>]*)>", function(match)
            if not match:match("/$") then
                return "<" .. match .. " />"
            end
            return "<" .. match .. ">"
        end)
    end
    
    return result
end

-- Function to check and fix tag balance
local function fix_tag_balance(text)
    if not text then return text end
    
    local result = text
    local tag_stack = {}
    
    -- Find all HTML tags in order
    for tag_match in text:gmatch("<[^>]+>") do
        local tag_name = tag_match:match("</?(%w+)")
        
        if tag_name then
            tag_name = tag_name:lower()
            
            if tag_match:match("^</") then
                -- Closing tag
                if #tag_stack > 0 and tag_stack[#tag_stack] == tag_name then
                    table.remove(tag_stack)
                end
            elseif not tag_match:match("/>$") and not self_closing_set[tag_name] then
                -- Opening tag (not self-closing)
                table.insert(tag_stack, tag_name)
            end
        end
    end
    
    -- Close any remaining unclosed tags
    for i = #tag_stack, 1, -1 do
        result = result .. "</" .. tag_stack[i] .. ">"
    end
    
    return result
end

-- Function to ensure HTML tags are VitePress compatible 
local function make_vitepress_compatible(text)
    if not text then return text end
    
    local result = text
    
    -- Fix self-closing tags
    result = fix_self_closing_tags(result)
    
    -- Ensure div tags with classes are properly structured
    -- VitePress can be sensitive to whitespace in certain contexts
    result = result:gsub('(<div class="[^"]*">)%s*\n%s*\n', '%1\n\n')
    
    -- Fix any remaining unclosed tags (safety net)
    result = fix_tag_balance(result)
    
    return result
end

-- Apply fixes to HTML content only (not code blocks)
function RawInline(elem)
    if elem.format == "html" then
        local fixed_text = make_vitepress_compatible(elem.text)
        if fixed_text ~= elem.text then
            elem.text = fixed_text
            return elem
        end
    end
    return elem
end

function RawBlock(elem)
    if elem.format == "html" then
        local fixed_text = make_vitepress_compatible(elem.text)
        if fixed_text ~= elem.text then
            elem.text = fixed_text
            return elem
        end
    end
    return elem
end