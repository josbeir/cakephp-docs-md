.. meta::
    :title: Table Conversion Test
    :description: Testing various RST table formats

Table Conversion Test
====================

Grid Tables
-----------

Basic grid table:

+--------+--------+--------+
| Header | Header | Header |
+========+========+========+
| Row 1  | Data 1 | Data 2 |
+--------+--------+--------+
| Row 2  | Data 3 | Data 4 |
+--------+--------+--------+

Grid table without header separator:

+--------+--------+--------+
| Col 1  | Col 2  | Col 3  |
+--------+--------+--------+
| Data 1 | Data 2 | Data 3 |
+--------+--------+--------+

Simple Tables with Header Separators
------------------------------------

Basic simple table:

=======  ========
Column 1  Column 2
=======  ========
Row 1     Data 1
Row 2     Data 2
=======  ========

Simple table with different column widths:

============  =================  ======
Short Column  Very Long Column    Number
============  =================  ======
A             This is long text   1
B             More text           2
C             Even more text      3
============  =================  ======

Simple Tables with Header Underlines
------------------------------------

Table with header underlines:

Column 1  Column 2  Column 3
--------  --------  --------
Row 1     Data 1    Value 1
Row 2     Data 2    Value 2
Row 3     Data 3    Value 3

Table with mixed separators:

Header A  Header B
========  --------
Cell A1   Cell B1
Cell A2   Cell B2

Complex Tables
--------------

Table with empty cells:

+--------+--------+--------+
| Name   | Age    | City   |
+========+========+========+
| John   | 25     |        |
+--------+--------+--------+
| Jane   |        | London |
+--------+--------+--------+

Table with special characters:

+--------+--------+--------+
| Symbol | Name   | Value  |
+========+========+========+
| @      | At     | 64     |
+--------+--------+--------+
| #      | Hash   | 35     |
+--------+--------+--------+
| $      | Dollar | 36     |
+--------+--------+--------+

Edge Cases
----------

Single row table:

+--------+
| Single |
+========+
| Row    |
+--------+

Table with very long content:

+--------+--------+--------+
| Short  | Very Long Content That Should Wrap Properly In The Table Cell |
+========+================================================================+
| A      | This is a very long piece of text that demonstrates how the table |
|        | should handle content that spans multiple lines in the source.     |
+--------+--------+--------+--------+--------+--------+--------+--------+--------+

Malformed Tables (like in hash.md)
-----------------------------------

Table with empty first cells (continuation rows):

| Expression | Definition |
| `{n}` | Represents a numeric key. Will match |
|  | any string or numeric key. |
| `{s}` | Represents a string. Will match any |
|  | string value including numeric string |
|  | values. |
| `{*}` | Matches any value. |
| `Foo` | Matches keys with the exact same value. |

Another malformed table:

| Matcher | Definition |
| `[id]`                       | Match elements with a given array key.     |
| `[id=2]` | Match elements with id equal to 2. |
| `[id!=2]` | Match elements with id not equal to 2. |
| `[id>2]` | Match elements with id greater than 2. |
| `[id>=2]` | Match elements with id greater than |
|  | or equal to 2. |
| `[id<2]` | Match elements with id less than 2 |
| `[id<=2]` | Match elements with id less than |
|  | or equal to 2. |
| `[text=/.../]` | Match elements that have values matching |
|  | the regular expression inside `...`. |

After Tables
------------

This content comes after the tables and should not be affected by the table conversion.