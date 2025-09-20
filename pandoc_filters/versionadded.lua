-- Version directives filter: Handle Sphinx versionadded and versionchanged directives
-- Converts versionadded and versionchanged divs to VitePress custom containers

function Div(div)
    local classes = div.attr.classes

    -- Check if this is a versionadded or versionchanged directive
    for _, class in ipairs(classes) do
        if class == "versionadded" or class == "versionchanged" then
            -- Extract version and message from the div content
            local version = ""
            local message_parts = {}
            local found_version = false

            for _, block in ipairs(div.content) do
                if block.tag == "Para" then
                    for _, inline in ipairs(block.content) do
                        if inline.tag == "Str" then
                            local text = inline.text
                            -- Check if this looks like a version number
                            if not found_version and text:match("^[%d%.]+$") then
                                version = text
                                found_version = true
                            else
                                table.insert(message_parts, text)
                            end
                        elseif inline.tag == "Space" then
                            table.insert(message_parts, " ")
                        elseif inline.tag == "Code" then
                            table.insert(message_parts, "`" .. inline.text .. "`")
                        end
                    end
                end
            end

            local message = table.concat(message_parts, ""):gsub("^%s+", ""):gsub("%s+$", "")

            -- Create the VitePress container with appropriate title
            local container_lines = {}
            local title = class == "versionadded" and "Added in version " or "Changed in version "
            table.insert(container_lines, "::: info " .. title .. version)

            if message ~= "" then
                table.insert(container_lines, message)
            end

            table.insert(container_lines, ":::")

            local container_text = table.concat(container_lines, "\n")

            -- Return as raw markdown
            return pandoc.RawBlock("markdown", container_text)
        end
    end

    return div
end