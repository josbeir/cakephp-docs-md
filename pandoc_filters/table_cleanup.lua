-- Table cleanup filter: Remove redundant row separators in GitHub Flavored Markdown tables
-- This filter removes table rows that contain only dashes (separator rows) except for the header separator

-- Import shared utilities
-- Get the directory where this filter is located
local filter_dir = debug.getinfo(1, "S").source:match("@(.*/)")  or ""
-- Add the filter directory to the package path
package.path = package.path .. ";" .. filter_dir .. "?.lua"
-- Now require utils from the same directory
local utils = require('utils')

-- Helper function to check if a table row contains only separator characters
local function is_separator_row(row)
    if not row or not row.cells then
        return false
    end

    -- row is a Row object, which has .cells property
    for _, cell in ipairs(row.cells) do
        -- Get the text content of the cell using cell.contents
        local cell_text = pandoc.utils.stringify(cell.contents)

        -- Remove whitespace
        cell_text = utils.trim(cell_text)

        -- Check if the cell contains only separator characters (-, |, spaces)
        -- An empty cell is also considered a separator
        if cell_text ~= "" and not cell_text:match("^[-|%s]+$") then
            return false
        end
    end

    return true
end

-- Process Table elements to remove redundant separator rows
function Table(tbl)
    if not tbl.bodies or #tbl.bodies == 0 then
        return tbl
    end

    -- Process each table body
    for body_idx, body in ipairs(tbl.bodies) do
        if body.body and #body.body > 0 then
            local new_rows = {}
            local header_processed = false

            for _, row in ipairs(body.body) do
                -- Skip separator rows, but keep track if we've seen the header
                if is_separator_row(row) then
                    -- Skip this row - it's a redundant separator
                    -- Note: GFM tables already have their header separator handled by pandoc
                    goto continue
                else
                    -- Keep this row
                    table.insert(new_rows, row)
                    header_processed = true
                end

                ::continue::
            end

            -- Update the body with cleaned rows
            body.body = new_rows
        end
    end

    return tbl
end

-- Also handle any raw markdown that might contain malformed tables
function RawBlock(elem)
    if elem.format == "markdown" then
        local text = elem.text

        -- Look for table patterns with redundant separator rows
        -- Pattern: line with only dashes and pipes between actual table rows
        local lines = {}
        for line in text:gmatch("[^\r\n]+") do
            table.insert(lines, line)
        end

        local new_lines = {}
        local in_table = false
        local header_separator_found = false

        for i, line in ipairs(lines) do
            -- Check if this looks like a table line
            if line:match("^%s*|.*|%s*$") then
                in_table = true

                -- Check if this is a separator row (contains only -, |, and spaces)
                local is_separator = line:match("^%s*|[%s|-]*|%s*$") ~= nil

                if is_separator then
                    -- This is a separator row
                    if not header_separator_found then
                        -- Keep the first separator (header separator)
                        table.insert(new_lines, line)
                        header_separator_found = true
                    end
                    -- Skip additional separators
                else
                    -- This is a data row, keep it
                    table.insert(new_lines, line)
                end
            else
                -- Not a table line
                in_table = false
                header_separator_found = false
                table.insert(new_lines, line)
            end
        end

        local new_text = table.concat(new_lines, "\n")
        if new_text ~= text then
            return pandoc.RawBlock("markdown", new_text)
        end
    end

    return elem
end