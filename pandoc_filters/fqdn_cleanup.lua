-- FQDN cleanup filter
-- Cleans up Fully Qualified Domain Name class references by:
-- 1. Converting double backslashes to single backslashes in backticked content
-- 2. Removing tilde (~) symbols in backticked content
-- This filter should run after php_domain.lua which creates the double backslashes

function Code(elem)
    local text = elem.text

    -- Check if this contains FQDN patterns (double backslashes with namespace/class structure)
    if text:match("\\\\") then
        -- Remove tilde symbols
        text = text:gsub("~", "")

        -- Convert multiple consecutive backslashes to single backslashes
        -- Handle cases like \\\\ or \\\\\\\\ by repeatedly reducing them
        while text:match("\\\\") do
            text = text:gsub("\\\\", "\\")
        end

        -- Return modified Code element
        return pandoc.Code(text, elem.attr)
    end

    return elem
end

-- Also handle inline markdown code spans that might contain FQDN references
function Str(elem)
    local text = elem.text

    -- Only process strings that contain both backticks and double backslashes
    if text:match("`[^`]*\\\\[^`]*`") then
        -- Process backticked content
        text = text:gsub("`([^`]*)`", function(content)
            -- Remove tilde symbols in backticked content
            content = content:gsub("~", "")

            -- Convert multiple consecutive backslashes to single backslashes in backticked content
            while content:match("\\\\") do
                content = content:gsub("\\\\", "\\")
            end

            return "`" .. content .. "`"
        end)

        return pandoc.Str(text)
    end

    return elem
end

-- Handle RawInline markdown that might contain FQDN references
function RawInline(elem)
    if elem.format == "markdown" then
        local text = elem.text

        -- Check if this contains backticked FQDN patterns
        if text:match("`[^`]*\\\\[^`]*`") then
            -- Process backticked content
            text = text:gsub("`([^`]*)`", function(content)
                -- Remove tilde symbols in backticked content
                content = content:gsub("~", "")

                -- Convert multiple consecutive backslashes to single backslashes in backticked content
                while content:match("\\\\") do
                    content = content:gsub("\\\\", "\\")
                end

                return "`" .. content .. "`"
            end)

            return pandoc.RawInline("markdown", text)
        end
    end

    return elem
end