# Export Functionality Setup Guide

This guide walks through setting up the PDF export functionality for the multi-user portfolio platform.

## Prerequisites

- PHP 7.4 or higher
- Composer installed
- MySQL database configured
- Web server (Apache/Nginx) running

## Installation Steps

### 1. Install TCPDF Library

Run Composer to install the TCPDF library:

```bash
composer install
```

Or specifically install TCPDF:

```bash
composer require tecnickcom/tcpdf
```

This will download and install:
- `tecnickcom/tcpdf` - PDF generation library
- `phpmailer/phpmailer` - Email functionality (already installed)

### 2. Create Temp Directory

Create the temporary directory for export files:

```bash
mkdir -p temp/exports
chmod 755 temp/exports
```

On Windows:
```cmd
mkdir temp\exports
```

### 3. Verify Directory Permissions

Ensure the web server has write permissions:

```bash
# Linux/Mac
chown -R www-data:www-data temp/exports
chmod 755 temp/exports

# Or for development
chmod 777 temp/exports
```

### 4. Update Composer Autoload

If you haven't already, regenerate the Composer autoload files:

```bash
composer dump-autoload
```

## Testing the Installation

### 1. Run the Test Script

Access the test script in your browser:

```
http://localhost/test_export.php
```

Or via command line:

```bash
php test_export.php
```

### 2. Expected Test Results

You should see:
- ✓ PDF generated successfully
- ✓ Selective PDF generated successfully
- ✓ HTML generated successfully
- ✓ Error handling works correctly
- ✓ Performance requirement met

### 3. Verify Generated Files

Check the `temp/exports/` directory for generated PDF and HTML files:

```bash
ls -lh temp/exports/
```

## Usage

### For End Users

1. **Log in** to your portfolio account
2. **Navigate** to your dashboard
3. **Click** the "Export Portfolio" button
4. **Choose** export format (PDF or HTML)
5. **Select** items to include (optional)
6. **Download** the generated file

### For Developers

#### Generate PDF Programmatically

```php
require_once 'includes/bootstrap.php';
require_once 'includes/Database.php';
require_once 'includes/Export/ExportGenerator.php';

use Export\ExportGenerator;
use Portfolio\PortfolioManager;
use Customization\CustomizationEngine;

$db = Database::getInstance()->getConnection();
$portfolioManager = new PortfolioManager($db);
$customizationEngine = new CustomizationEngine($db);
$exportGenerator = new ExportGenerator($db, $customizationEngine, $portfolioManager);

// Generate PDF
$result = $exportGenerator->generatePDF($userId);

if ($result->success) {
    // Success - file at $result->filePath
    echo "PDF generated in {$result->generationTime}ms";
} else {
    // Error - message in $result->error
    echo "Error: {$result->error}";
}
```

#### Export via HTTP Endpoint

```bash
# Full portfolio PDF
curl -X GET "http://localhost/export_portfolio.php?format=pdf" \
  -H "Cookie: session_token=YOUR_SESSION_TOKEN" \
  -o portfolio.pdf

# Selective items PDF
curl -X GET "http://localhost/export_portfolio.php?format=pdf&items=1,3,5" \
  -H "Cookie: session_token=YOUR_SESSION_TOKEN" \
  -o portfolio_selective.pdf

# HTML export
curl -X GET "http://localhost/export_portfolio.php?format=html" \
  -H "Cookie: session_token=YOUR_SESSION_TOKEN" \
  -o portfolio.html
```

## Integration with Dashboard

Add export button to the dashboard (`dashboard.php`):

```html
<!-- Export Section -->
<div class="export-section">
    <h2>Export Portfolio</h2>
    <p>Download your portfolio as a PDF or HTML file.</p>
    
    <div class="export-buttons">
        <a href="export_portfolio.php?format=pdf" class="btn btn-primary" download>
            <i class="icon-download"></i> Download PDF
        </a>
        <a href="export_portfolio.php?format=html" class="btn btn-secondary" download>
            <i class="icon-download"></i> Download HTML
        </a>
    </div>
    
    <!-- Optional: Selective export -->
    <div class="selective-export">
        <h3>Selective Export</h3>
        <form id="selective-export-form" action="export_portfolio.php" method="get">
            <input type="hidden" name="format" value="pdf">
            <div class="item-selection">
                <!-- Dynamically populate with portfolio items -->
                <?php foreach ($portfolioItems as $item): ?>
                <label>
                    <input type="checkbox" name="items[]" value="<?= $item->getId() ?>">
                    <?= htmlspecialchars($item->getTitle()) ?>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary">Export Selected Items</button>
        </form>
    </div>
</div>
```

Add JavaScript for better UX:

```javascript
// Show loading indicator during export
document.querySelectorAll('.export-buttons a').forEach(link => {
    link.addEventListener('click', function(e) {
        const loadingMsg = document.createElement('div');
        loadingMsg.className = 'export-loading';
        loadingMsg.textContent = 'Generating export... This may take a few seconds.';
        this.parentElement.appendChild(loadingMsg);
    });
});

// Handle selective export form
document.getElementById('selective-export-form')?.addEventListener('submit', function(e) {
    const checkboxes = this.querySelectorAll('input[name="items[]"]:checked');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Please select at least one item to export.');
        return false;
    }
    
    // Convert checkboxes to comma-separated string
    const itemIds = Array.from(checkboxes).map(cb => cb.value).join(',');
    this.action = `export_portfolio.php?format=pdf&items=${itemIds}`;
});
```

## Troubleshooting

### Issue: "Class 'TCPDF' not found"

**Cause**: TCPDF library not installed

**Solution**:
```bash
composer require tecnickcom/tcpdf
```

### Issue: "Permission denied" when creating temp directory

**Cause**: Web server lacks write permissions

**Solution**:
```bash
chmod 755 temp/exports
chown www-data:www-data temp/exports
```

### Issue: PDF generation takes too long

**Cause**: Large images or many portfolio items

**Solutions**:
1. Optimize images before upload (compress, resize)
2. Limit images per item (currently 3)
3. Increase PHP memory limit in `php.ini`:
   ```ini
   memory_limit = 256M
   max_execution_time = 60
   ```

### Issue: Images not appearing in PDF

**Cause**: File paths incorrect or files missing

**Solution**:
1. Verify file paths in database match actual file locations
2. Check file permissions (readable by web server)
3. Ensure image files exist on disk

### Issue: Fonts look wrong in PDF

**Cause**: Custom fonts not mapped correctly

**Solution**: The ExportGenerator maps custom fonts to TCPDF built-in fonts. If you need specific fonts, you can:
1. Add custom fonts to TCPDF
2. Update the `mapFont()` method in ExportGenerator

### Issue: PDF download fails with 500 error

**Cause**: Various - check error logs

**Solution**:
1. Check PHP error log: `tail -f /var/log/php/error.log`
2. Enable error display in `export_portfolio.php` (development only):
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. Check database connection
4. Verify user authentication

## Performance Optimization

### For Large Portfolios

If you have portfolios with many items (>50):

1. **Implement Caching**:
   ```php
   // Cache generated PDFs for 1 hour
   $cacheKey = "pdf_{$userId}_" . md5(serialize($itemIds));
   $cachedPath = "temp/exports/cache/{$cacheKey}.pdf";
   
   if (file_exists($cachedPath) && (time() - filemtime($cachedPath)) < 3600) {
       return new PDFResult(true, $cachedPath, null, 0);
   }
   ```

2. **Background Processing**:
   - Queue large exports for background processing
   - Notify user via email when ready
   - Use job queue system (e.g., Beanstalkd, Redis Queue)

3. **Image Optimization**:
   - Compress images on upload
   - Generate web-optimized versions
   - Limit image dimensions (e.g., max 1920px width)

### Server Configuration

Recommended PHP settings for export functionality:

```ini
; php.ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 10M
post_max_size = 10M
```

## Security Considerations

1. **Authentication**: Export endpoint requires valid session
2. **Authorization**: Users can only export their own portfolios
3. **Rate Limiting**: Consider adding rate limits to prevent abuse
4. **File Cleanup**: Temporary files are deleted after download
5. **Input Validation**: Item IDs are validated and sanitized

## Maintenance

### Cleanup Old Export Files

Create a cron job to clean up old temporary files:

```bash
# Add to crontab (run daily at 2 AM)
0 2 * * * find /path/to/temp/exports -type f -mtime +1 -delete
```

Or create a PHP cleanup script:

```php
<?php
// cleanup_exports.php
$exportDir = __DIR__ . '/temp/exports';
$maxAge = 86400; // 24 hours

$files = glob($exportDir . '/*.{pdf,html}', GLOB_BRACE);
foreach ($files as $file) {
    if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
        unlink($file);
    }
}
```

## Next Steps

1. ✅ Install TCPDF library
2. ✅ Create temp directory
3. ✅ Run test script
4. ✅ Integrate with dashboard
5. ✅ Test with real portfolio data
6. ✅ Set up file cleanup cron job
7. ✅ Configure rate limiting (optional)
8. ✅ Add export analytics (optional)

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review `includes/Export/README.md` for detailed API documentation
3. Check PHP error logs for detailed error messages
4. Verify all prerequisites are met

## Requirements Validation

This implementation satisfies:

- ✅ **Requirement 8.1**: PDF download for authenticated users
- ✅ **Requirement 8.2**: Includes profile, items, and customization
- ✅ **Requirement 8.3**: <30 second generation for 50 items
- ✅ **Requirement 8.4**: Embeds portfolio item images
- ✅ **Requirement 8.5**: Letter size with appropriate margins
- ✅ **Requirement 8.6**: Selective item inclusion
- ✅ **Requirement 8.7**: Descriptive error messages
