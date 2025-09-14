<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertTables
{
    public function __invoke(string $content): string
    {
        // Split content into lines for processing
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for table directive first (.. table::)
            if (preg_match('/^\s*\.\. table::\s*(.*)$/', $line, $matches)) {
                $i = $this->processTableDirective($lines, $i, $result, trim($matches[1]));
                continue;
            }

            // Check for grid table (lines starting with +===+ or +---+)
            if (preg_match('/^\s*\+[=\-+]+/', $line)) {
                $i = $this->processGridTable($lines, $i, $result);
                continue;
            }

            // Check for simple table (lines with === separators)
            if (preg_match('/^\s*=+\s+=+/', $line)) {
                $i = $this->processSimpleTable($lines, $i, $result, false);

                continue;
            }

            // Check for headerless simple table (data followed by === line)
            if (
                $i + 1 < count($lines) &&
                preg_match('/^\s*=+\s+=+/', $lines[$i + 1]) &&
                !empty(trim($line))
            ) {
                $i = $this->processSimpleTable($lines, $i, $result, true);
                continue;
            }

            // Check for header underline table (header row followed by --- underline)
            // Exclude frontmatter (lines that are exactly ---)
            if (
                $i + 1 < count($lines) &&
                preg_match('/^\s*[\-=]+(\s+[\-=]+)*\s*$/', $lines[$i + 1]) &&
                trim($lines[$i + 1]) !== '---' && // Exclude frontmatter closing
                !empty(trim($line)) &&
                !preg_match('/^\s*[\-=+|]/', $line) &&
                !preg_match('/^[^:]+:\s/', $line) &&
                $this->isValidHeaderUnderlineTable($line, $lines[$i + 1])
            ) {  // Exclude frontmatter key:value lines
                $i = $this->processHeaderUnderlineTable($lines, $i, $result);
                continue;
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    private function processTableDirective(array $lines, int $start, array &$result, string $caption): int
    {
        $i = $start + 1;

        // Skip empty lines and directive options
        while ($i < count($lines)) {
            $line = $lines[$i];
            if (empty(trim($line))) {
                $i++;
                continue;
            }

            if (preg_match('/^\s+:[\w-]+:/', $line)) {
                $i++;
                continue;
            }

            break;
        }

        // Find the actual table content
        if ($i < count($lines)) {
            if (preg_match('/^\s*\+[=\-+]+/', $lines[$i])) {
                $i = $this->processGridTable($lines, $i, $result, $caption);
            } elseif (preg_match('/^\s*=+\s+=+/', $lines[$i])) {
                $i = $this->processSimpleTable($lines, $i, $result, false, $caption);
            }
        }

        return $i;
    }

    private function processGridTable(array $lines, int $start, array &$result, string $caption = ''): int
    {
        $tableLines = [];
        $i = $start;

        // Collect all grid table lines
        while ($i < count($lines)) {
            $line = $lines[$i];
            if (
                preg_match('/^\s*\+[=\-+|]*\+?\s*$/', $line) ||
                preg_match('/^\s*\|.*\|\s*$/', $line)
            ) {
                $tableLines[] = $line;
                $i++;
            } else {
                break;
            }
        }

        if ($tableLines === []) {
            return $i;
        }

        $table = $this->parseGridTable($tableLines);
        if ($table) {
            $markdown = $this->renderMarkdownTable($table, $caption);
            $result = array_merge($result, explode("\n", $markdown));
            $result[] = ''; // Add empty line after table
        }

        return $i;
    }

    private function processSimpleTable(array $lines, int $start, array &$result, bool $headerless, string $caption = ''): int
    {
        $tableLines = [];
        $i = $start;

        // For headerless tables, include the data line before the separator
        if ($headerless && $start > 0) {
            $tableLines[] = $lines[$start];
        }

        // Collect all simple table lines
        $separatorCount = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            if (preg_match('/^\s*[=\-]+(\s+[=\-]+)*\s*$/', $line)) {
                $tableLines[] = $line;
                $separatorCount++;
                $i++;
                // Check if this looks like a final separator (equals signs) vs row separator (dashes)
                // We need at least 3 separators for a complete table: opening, header, closing
                if (preg_match('/^\s*=+(\s+=+)*\s*$/', $line) && $separatorCount >= 3) {
                    // Found final separator with equals - end of table
                    $i++; // Move past the final separator
                    break;
                }
            } elseif (!empty(trim($line)) && !preg_match('/^\s*$/', $line)) {
                $tableLines[] = $line;
                $i++;
            } elseif (empty(trim($line))) {
                // Allow empty lines within table content, but stop if we hit multiple empty lines
                $emptyCount = 0;
                $lookAhead = $i;
                while ($lookAhead < count($lines) && empty(trim($lines[$lookAhead]))) {
                    $emptyCount++;
                    $lookAhead++;
                }

                if ($emptyCount > 1) {
                    break; // Multiple empty lines likely indicate end of table
                }

                $tableLines[] = $line;
                $i++;
            } else {
                break;
            }
        }

        if (count($tableLines) < 2) {
            return $i;
        }

        $table = $this->parseSimpleTable($tableLines, $headerless);
        if ($table) {
            $markdown = $this->renderMarkdownTable($table, $caption);
            $result = array_merge($result, explode("\n", $markdown));
            $result[] = ''; // Add empty line after table
        }

        return $i;
    }

    private function processHeaderUnderlineTable(array $lines, int $start, array &$result, string $caption = ''): int
    {
        $i = $start;
        $headerLine = $lines[$i];
        $underlineLine = $lines[$i + 1];

        // Extract column boundaries from underline
        $colBoundaries = $this->getSimpleTableColumns($underlineLine);
        if ($colBoundaries === []) {
            return $start + 1; // Skip if we can't parse columns
        }

        // Parse header
        $headers = $this->extractSimpleCells($headerLine, $colBoundaries);

        // Collect data rows (everything after underline until empty line or non-table content)
        $rows = [];
        $i += 2; // Skip header and underline

        while ($i < count($lines)) {
            $line = $lines[$i];

            if (empty(trim($line))) {
                // Empty line ends table
                break;
            }

            if (preg_match('/^\s*[\-=+|]/', $line)) {
                // Another table structure starting
                break;
            }

            // Parse as data row
            $cells = $this->extractSimpleCells($line, $colBoundaries);
            $rows[] = array_map('trim', $cells);
            $i++;
        }

        if ($headers !== [] && $rows !== []) {
            $table = [
                'headers' => array_map('trim', $headers),
                'rows' => $rows,
                'aligns' => array_fill(0, count($headers), 'left'),
            ];

            $markdown = $this->renderMarkdownTable($table, $caption);
            $result = array_merge($result, explode("\n", $markdown));
            $result[] = ''; // Add empty line after table
        }

        return $i;
    }

    private function isValidHeaderUnderlineTable(string $headerLine, string $underlineLine): bool
    {
        // Extract column boundaries from underline
        $colBoundaries = $this->getSimpleTableColumns($underlineLine);
        if ($colBoundaries === []) {
            return false;
        }

        // Check if header line has reasonable content for the column structure
        $headers = $this->extractSimpleCells($headerLine, $colBoundaries);
        if ($headers === []) {
            return false;
        }

        // Validate that we have actual header content (not empty or too short)
        $validHeaders = 0;
        foreach ($headers as $header) {
            $trimmed = trim($header);
            if (!empty($trimmed) && strlen($trimmed) > 1) {
                $validHeaders++;
            }
        }

        // Require at least 2 valid headers for a table
        if ($validHeaders < 2) {
            return false;
        }

        // Additional check: underline segments should have reasonable length
        $underlineSegments = preg_split('/\s+/', trim($underlineLine));
        foreach ($underlineSegments as $segment) {
            if (strlen($segment) < 3) { // Each column underline should be at least 3 chars
                return false;
            }
        }

        return true;
    }

    private function parseGridTable(array $lines): ?array
    {
        if ($lines === []) {
            return null;
        }

        // Find column positions from first border line
        $firstLine = $lines[0];
        $colPositions = [];
        for ($i = 0; $i < strlen($firstLine); $i++) {
            if ($firstLine[$i] === '+') {
                $colPositions[] = $i;
            }
        }

        if (count($colPositions) < 2) {
            return null;
        }

        $headers = [];
        $rows = [];
        $currentRow = [];
        $inHeader = false;

        foreach ($lines as $line) {
            if (preg_match('/^\s*\+[=+]+/', $line)) {
                // Header separator (===) or table border
                if ($currentRow !== []) {
                    if (!$inHeader) {
                        $headers = $currentRow;
                        $inHeader = true;
                    } else {
                        $rows[] = $currentRow;
                    }

                    $currentRow = [];
                }
            } elseif (preg_match('/^\s*\+[-+]+/', $line)) {
                // Row separator (---)
                if ($currentRow !== []) {
                    $rows[] = $currentRow;
                    $currentRow = [];
                }
            } elseif (preg_match('/^\s*\|/', $line)) {
                // Data row
                $cells = $this->extractGridCells($line);
                if ($currentRow === []) {
                    $currentRow = $cells;
                } else {
                    // Merge with existing row (for multi-line cells)
                    $counter = count($cells);
                    // Merge with existing row (for multi-line cells)
                    for ($i = 0; $i < $counter; $i++) {
                        if (isset($currentRow[$i])) {
                            $currentRow[$i] = trim($currentRow[$i] . ' ' . trim($cells[$i]));
                        }
                    }
                }
            }
        }

        // Add final row if exists
        if ($currentRow !== []) {
            $rows[] = $currentRow;
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'aligns' => array_fill(0, count($headers), 'left'),
        ];
    }

    private function parseSimpleTable(array $lines, bool $headerless): ?array
    {
        if ($lines === []) {
            return null;
        }

        // Find separator lines (=== or ---)
        $separatorLines = [];
        foreach ($lines as $idx => $line) {
            if (preg_match('/^\s*[=\-]+(\s+[=\-]+)*\s*$/', $line)) {
                $separatorLines[] = $idx;
            }
        }

        if (count($separatorLines) < 2) {
            return null; // Need at least 2 separators for a valid simple table
        }

        // Extract column boundaries from first separator
        $firstSep = $lines[$separatorLines[0]];
        $colBoundaries = $this->getSimpleTableColumns($firstSep);

        if ($colBoundaries === []) {
            return null;
        }

        $headers = [];
        $rows = [];

        if (!$headerless) {
            // Extract headers (between first and second separator)
            $headerLines = [];
            for ($i = $separatorLines[0] + 1; $i < $separatorLines[1]; $i++) {
                if (isset($lines[$i]) && !empty(trim($lines[$i]))) {
                    $headerLines[] = $lines[$i];
                }
            }

            // Combine header lines
            $headers = array_fill(0, count($colBoundaries), '');
            foreach ($headerLines as $headerLine) {
                $headerCells = $this->extractSimpleCells($headerLine, $colBoundaries);
                for ($j = 0; $j < count($headerCells) && $j < count($headers); $j++) {
                    $headers[$j] = trim($headers[$j] . ' ' . trim($headerCells[$j]));
                }
            }

            $headers = array_map('trim', $headers);
        } else {
            // Headerless table
            $headers = array_fill(0, count($colBoundaries), '');
        }

        // Extract data rows (after header separator, before final separator)
        if (count($separatorLines) >= 2) {
            $dataStartIdx = $separatorLines[0] + 1; // After first separator (header separator)
            $dataEndIdx = $separatorLines[count($separatorLines) - 1] - 1; // Before last separator

            // Skip header rows - find the actual data start after second separator
            if (!$headerless) {
                $dataStartIdx = $separatorLines[1] + 1; // After second separator (data separator)
            }
        } else {
            $dataStartIdx = 0;
            $dataEndIdx = count($lines) - 1;
        }

        // Process data lines, handling intermediate row separators
        $currentRowData = [];
        for ($i = $dataStartIdx; $i <= $dataEndIdx; $i++) {
            $line = $lines[$i] ?? '';

            // Check if this is a row separator (dashes only, not equals)
            if (preg_match('/^\s*-+(\s+-+)*\s*$/', $line)) {
                // Row separator - finalize current row if exists
                if ($currentRowData !== []) {
                    $rows[] = array_map('trim', $currentRowData);
                    $currentRowData = [];
                }
            } elseif (!empty(trim($line)) && !preg_match('/^\s*[=\-]+(\s+[=\-]+)*\s*$/', $line)) {
                // Data line - extract cells
                $cells = $this->extractSimpleCells($line, $colBoundaries);
                if ($currentRowData === []) {
                    $currentRowData = $cells;
                } else {
                    // Multi-line row - merge with current row
                    for ($j = 0; $j < count($cells) && $j < count($currentRowData); $j++) {
                        $currentRowData[$j] = trim($currentRowData[$j] . ' ' . trim($cells[$j]));
                    }
                }
            }
        }

        // Add final row if exists
        if ($currentRowData !== []) {
            $rows[] = array_map('trim', $currentRowData);
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
            'aligns' => array_fill(0, count($headers), 'left'),
        ];
    }

    private function extractGridCells(string $line): array
    {
        $cells = [];

        // Grid table lines look like: | content | content |
        // We need to find the actual | separators, not rely on + positions from borders
        $pipeSeparators = [];

        // Find all | characters in the line
        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] === '|') {
                $pipeSeparators[] = $i;
            }
        }

        if (count($pipeSeparators) < 2) {
            return $cells; // Need at least 2 pipes for cell content
        }

        // Extract content between pipe separators
        for ($i = 0; $i < count($pipeSeparators) - 1; $i++) {
            $start = $pipeSeparators[$i] + 1;
            $end = $pipeSeparators[$i + 1];
            $cellContent = substr($line, $start, $end - $start);
            $cells[] = trim($cellContent);
        }

        return $cells;
    }

    private function getSimpleTableColumns(string $separatorLine): array
    {
        // Find column boundaries by identifying === or --- segments
        $positions = [];
        $inSepSeries = false;
        $sepStart = 0;

        for ($i = 0; $i <= strlen($separatorLine); $i++) {
            $char = $i < strlen($separatorLine) ? $separatorLine[$i] : ' ';

            if ($char === '=' || $char === '-') {
                if (!$inSepSeries) {
                    $sepStart = $i;
                    $inSepSeries = true;
                }
            } elseif ($inSepSeries) {
                // End of separator series
                $positions[] = $sepStart;
                // Start of column
                $positions[] = $i;
                // End of column
                $inSepSeries = false;
            }
        }

        // Convert to proper column boundaries
        // Each pair represents (start, end) of a column's content area
        $colBoundaries = [];
        $counter = count($positions);
        for ($i = 0; $i < $counter; $i += 2) {
            if (isset($positions[$i + 1])) {
                $colBoundaries[] = [$positions[$i], $positions[$i + 1]];
            }
        }

        return $colBoundaries;
    }

    private function extractSimpleCells(string $line, array $colBoundaries): array
    {
        $cells = [];
        foreach ($colBoundaries as $boundary) {
            $start = $boundary[0];
            $end = $boundary[1];
            $cellContent = substr($line, $start, $end - $start);
            $cells[] = trim($cellContent);
        }

        return $cells;
    }

    private function renderMarkdownTable(array $table, string $caption = ''): string
    {
        $headers = $table['headers'] ?? [];
        $rows = $table['rows'] ?? [];
        $aligns = $table['aligns'] ?? [];

        if (empty($headers) && empty($rows)) {
            return '';
        }

        $result = [];

        // Add caption if present (Pandoc-style: ": Caption")
        if (!empty($caption)) {
            $result[] = ': ' . $caption;
            $result[] = '';
        }

        // If no headers, create empty ones for pipe table compatibility
        if (empty($headers) && !empty($rows)) {
            $headers = array_fill(0, count($rows[0]), '');
        }

        // Build header row
        if (!empty($headers)) {
            $headerRow = '| ' . implode(' | ', array_map([$this, 'escapePipeTableCell'], $headers)) . ' |';
            $result[] = $headerRow;

            // Build separator row
            $separators = [];
            foreach ($headers as $i => $header) {
                $align = $aligns[$i] ?? 'left';
                switch ($align) {
                    case 'center':
                        $separators[] = ':---:';
                        break;
                    case 'right':
                        $separators[] = '---:';
                        break;
                    default:
                        $separators[] = '---';
                        break;
                }
            }

            $separatorRow = '| ' . implode(' | ', $separators) . ' |';
            $result[] = $separatorRow;
        }

        // Build data rows
        foreach ($rows as $row) {
            $rowData = '| ' . implode(' | ', array_map([$this, 'escapePipeTableCell'], $row)) . ' |';
            $result[] = $rowData;
        }

        return implode("\n", $result);
    }

    private function escapePipeTableCell(string $content): string
    {
        // Escape pipes and trim content
        $content = trim($content);
        $content = str_replace('|', '\\|', $content);

        // Convert line breaks to spaces
        $content = preg_replace('/\s+/', ' ', $content);

        return $content;
    }
}
