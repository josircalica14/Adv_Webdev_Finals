# Export Generator

The ExportGenerator class handles PDF and HTML export functionality for student portfolios.

## Features

- **PDF Generation**: Creates professional PDF documents with portfolio content
- **Customization Support**: Applies user's customization settings (colors, fonts, layout)
- **Image Embedding**: Includes portfolio item images in exports
- **Selective Export**: Allows exporting specific portfolio items
- **HTML Export**: Alternative export format for web viewing
- **Error Handling**: Comprehensive error messages for troubleshooting
- **Performance Optimized**: Targets <30 second generation for 50 items

## Requirements

### Dependencies

Install TCPDF library via Composer:

```bash
composer require tecnickcom/tcpdf
```

### Directory Structure

The export generator requires a temp directory for storing generated files:

```
temp/
  exports/
    (generated PDF and HTML files)
```

This directory is created automatically if it doesn't exist.

## Usage

### Basic PDF Export

```php
use Export\ExportGenerator;
use Portfolio\PortfolioManager;
use Customization\CustomizationEngine;

$db = Database::getInstance()->getConnection();
$portfolioManager = new PortfolioManager($db);
$customizationEngine = new CustomizationEngine($db);
$exportGenerator = new ExportGenerator($db, $customizationEngine, $portfolioManager);

// Generate PDF for user
$result = $exportGenerator->generatePDF($userId);

if ($result->success) {
    echo "PDF generated: " . $result->filePath;
    echo "Generation time: " . $result->generationTime . "ms";
} else {
    echo "Error: " . $result->error;
}
```

### Selective Item Export

```php
// Export only specific portfolio items
$itemIds = [1, 3, 5, 7];
$result = $exportGenerator->generatePDF($userId, $itemIds);
```

### HTML Export

```php
// Generate HTML version
$html = $exportGenerator->generateHTML($userId);

// Save to file
file_put_contents('portfolio.html', $html);
```

### Download Endpoint

Use the provided `export_portfolio.php` endpoint:

```
GET /export_portfolio.php?format=pdf
GET /export_portfolio.php?format=html
GET /export_portfolio.php?format=pdf&items=1,3,5
```

## PDF Structure

Generated PDFs include:

1. **Header Section**
   - Student name (large, primary color)
   - Program affiliation
   - Decorative line (accent color)

2. **Profile Section**
   - Profile photo (if available)
   - Bio/About section
   - Contact information

3. **Portfolio Items**
   - Grouped by type (Projects, Achievements, Milestones, Skills)
   - Item title (accent color)
   - Date (if available)
   - Description
   - Tags
   - Links (clickable in PDF)
   - Embedded images (up to 3 per item)

## Customization Application

The export generator applies user customization settings:

- **Primary Color**: Used for headings and main text accents
- **Accent Color**: Used for item titles and decorative elements
- **Heading Font**: Applied to all headings (mapped to TCPDF fonts)
- **Body Font**: Applied to body text (mapped to TCPDF fonts)

### Font Mapping

Custom fonts are mapped to TCPDF built-in fonts:

| Custom Font | TCPDF Font |
|-------------|------------|
| Roboto, Open Sans, Lato, Montserrat, Poppins, Arial | helvetica |
| Georgia, Times New Roman | times |
| Courier New | courier |

## Performance

- **Target**: <30 seconds for portfolios with 50 items
- **Optimization**: Images are limited to 3 per item
- **Image Size**: Images are embedded at 150 DPI for quality/size balance
- **Page Size**: Letter (8.5" x 11") with 0.5" margins

## Error Handling

The generator provides descriptive error messages:

- "User not found" - Invalid user ID
- "PDF generation failed: [details]" - TCPDF errors
- "Error generating HTML: [details]" - HTML generation errors

## Testing

Run the test script to verify functionality:

```bash
php test_export.php
```

Tests include:
- Full portfolio PDF generation
- Selective item export
- HTML export
- Error handling
- Performance validation

## Security Considerations

1. **Authentication Required**: Export endpoint requires valid session
2. **Access Control**: Users can only export their own portfolios
3. **File Cleanup**: Temporary PDF files are deleted after download
4. **Input Validation**: Item IDs are validated and sanitized

## Troubleshooting

### TCPDF Not Found

```
Fatal error: Class 'TCPDF' not found
```

**Solution**: Install TCPDF via Composer:
```bash
composer require tecnickcom/tcpdf
```

### Image Embedding Fails

```
PDF generation failed: Image file not found
```

**Solution**: Verify file paths in the `files` table are correct and files exist on disk.

### Slow Generation

If generation takes >30 seconds:

1. Reduce number of images per item (currently limited to 3)
2. Optimize image file sizes before upload
3. Consider caching portfolio data
4. Check server resources (memory, CPU)

### Permission Errors

```
mkdir(): Permission denied
```

**Solution**: Ensure web server has write permissions to `temp/exports/` directory:
```bash
chmod 755 temp/exports
```

## API Reference

### ExportGenerator Class

#### `generatePDF(int $userId, array $itemIds = []): PDFResult`

Generates PDF export of portfolio.

**Parameters:**
- `$userId` - User ID to export
- `$itemIds` - Optional array of specific item IDs to include

**Returns:** `PDFResult` object with:
- `success` (bool) - Whether generation succeeded
- `filePath` (string|null) - Path to generated PDF
- `error` (string|null) - Error message if failed
- `generationTime` (int) - Generation time in milliseconds

#### `generateHTML(int $userId, array $itemIds = []): string`

Generates HTML export of portfolio.

**Parameters:**
- `$userId` - User ID to export
- `$itemIds` - Optional array of specific item IDs to include

**Returns:** HTML string

### PDFResult Class

Result object returned by `generatePDF()`.

**Properties:**
- `bool $success` - Success status
- `?string $filePath` - Path to generated file
- `?string $error` - Error message
- `int $generationTime` - Generation time in ms

## Requirements Validation

This implementation validates the following requirements:

- **8.1**: PDF download functionality for authenticated users
- **8.2**: Includes profile information, portfolio items, and customization
- **8.3**: Optimized for <30 second generation (50 items)
- **8.4**: Embeds images from portfolio items
- **8.5**: Letter size (8.5" x 11") with appropriate margins
- **8.6**: Selective item inclusion support
- **8.7**: Descriptive error messages on failure

## Future Enhancements

Potential improvements:

1. **Caching**: Cache generated PDFs for repeated downloads
2. **Background Processing**: Queue large exports for background generation
3. **Custom Templates**: Allow users to choose PDF templates
4. **Watermarks**: Add optional watermarks or branding
5. **Multi-format**: Support additional formats (DOCX, Markdown)
6. **Compression**: Optimize PDF file size with compression
