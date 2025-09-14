<?php
declare(strict_types=1);

namespace Cake\DocsMD;

use Cake\DocsMD\ConvertSteps\ApplyQuirks;
use Cake\DocsMD\ConvertSteps\ConvertAdmonitions;
use Cake\DocsMD\ConvertSteps\ConvertCodeBlocks;
use Cake\DocsMD\ConvertSteps\ConvertContainers;
use Cake\DocsMD\ConvertSteps\ConvertCrossReferences;
use Cake\DocsMD\ConvertSteps\ConvertHeadings;
use Cake\DocsMD\ConvertSteps\ConvertImages;
use Cake\DocsMD\ConvertSteps\ConvertIncludes;
use Cake\DocsMD\ConvertSteps\ConvertIndentedPhpBlocks;
use Cake\DocsMD\ConvertSteps\ConvertLinks;
use Cake\DocsMD\ConvertSteps\ConvertLists;
use Cake\DocsMD\ConvertSteps\ConvertMiscDirectives;
use Cake\DocsMD\ConvertSteps\ConvertPhpDirectives;
use Cake\DocsMD\ConvertSteps\ConvertReferenceLabels;
use Cake\DocsMD\ConvertSteps\ConvertRstInlineCode;
use Cake\DocsMD\ConvertSteps\ConvertTables;
use Cake\DocsMD\ConvertSteps\ConvertVersionAdded;
use Cake\DocsMD\ConvertSteps\FixAbsolutePaths;
use Cake\DocsMD\ConvertSteps\FixBrokenMarkdownLinks;
use Cake\DocsMD\ConvertSteps\HandleMetaDirective;
use Cake\DocsMD\ConvertSteps\NormalizeIndentation;
use Cake\DocsMD\ConvertSteps\NormalizeSpacing;
use Cake\DocsMD\ConvertSteps\RemoveIndexDirectives;

class Converter
{
    public static function getPipeline(string $basePath = '', array $labelToDocumentMap = [], string $currentFile = ''): array
    {
        return [
            new HandleMetaDirective(),
            new ConvertReferenceLabels(),
            new ConvertVersionAdded(),
            new ConvertIncludes($currentFile),
            new ConvertMiscDirectives(),
            new ConvertHeadings(),
            new ConvertPhpDirectives(),
            new ConvertCrossReferences($basePath, $labelToDocumentMap, true, $currentFile),
            new ConvertAdmonitions(), // Moved before ConvertCodeBlocks
            new ConvertCodeBlocks(),
            new ConvertIndentedPhpBlocks(),
            new ConvertRstInlineCode(),
            new ConvertContainers(),
            new ConvertTables(),
            new ConvertImages(),
            new ConvertLinks($basePath, $currentFile),
            new ConvertLists(),
            new RemoveIndexDirectives(),
            new FixAbsolutePaths(),
            new FixBrokenMarkdownLinks(),
            new ApplyQuirks(),
            new NormalizeSpacing(),
            new NormalizeIndentation(),
        ];
    }
}
