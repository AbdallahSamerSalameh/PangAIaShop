# PowerShell script to create rounded favicon from logo1.png
# This script uses .NET System.Drawing to create rounded versions

Add-Type -AssemblyName System.Drawing

function Create-RoundedFavicon {
    param(
        [string]$InputPath,
        [string]$OutputPath,
        [int]$Size = 64,
        [int]$CornerRadius = 32,
        [string]$BackgroundColor = "White"
    )
    
    try {
        # Load the original image
        $originalImage = [System.Drawing.Image]::FromFile($InputPath)
        
        # Create a new bitmap
        $bitmap = New-Object System.Drawing.Bitmap($Size, $Size)
        $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
        
        # Set high quality rendering
        $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
        $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
        
        # Create clipping path (rounded rectangle)
        $path = New-Object System.Drawing.Drawing2D.GraphicsPath
        if ($CornerRadius -ge $Size / 2) {
            # Perfect circle
            $path.AddEllipse(0, 0, $Size, $Size)
        } else {
            # Rounded rectangle
            $path.AddArc(0, 0, $CornerRadius * 2, $CornerRadius * 2, 180, 90)
            $path.AddArc($Size - $CornerRadius * 2, 0, $CornerRadius * 2, $CornerRadius * 2, 270, 90)
            $path.AddArc($Size - $CornerRadius * 2, $Size - $CornerRadius * 2, $CornerRadius * 2, $CornerRadius * 2, 0, 90)
            $path.AddArc(0, $Size - $CornerRadius * 2, $CornerRadius * 2, $CornerRadius * 2, 90, 90)
            $path.CloseFigure()
        }
        
        # Set clipping region
        $graphics.SetClip($path)
        
        # Fill background
        $brush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromName($BackgroundColor))
        $graphics.FillPath($brush, $path)
        
        # Calculate image position (centered with some padding)
        $padding = $Size * 0.1  # 10% padding
        $contentSize = $Size - ($padding * 2)
        
        # Calculate scale to fit image in content area
        $scaleX = $contentSize / $originalImage.Width
        $scaleY = $contentSize / $originalImage.Height
        $scale = [Math]::Min($scaleX, $scaleY)
        
        $newWidth = $originalImage.Width * $scale
        $newHeight = $originalImage.Height * $scale
        
        # Center the image
        $x = ($Size - $newWidth) / 2
        $y = ($Size - $newHeight) / 2
        
        # Draw the image
        $destRect = New-Object System.Drawing.Rectangle($x, $y, $newWidth, $newHeight)
        $graphics.DrawImage($originalImage, $destRect)
        
        # Save the result
        $bitmap.Save($OutputPath, [System.Drawing.Imaging.ImageFormat]::Png)
        
        Write-Host "✅ Created rounded favicon: $OutputPath ($Size x $Size)" -ForegroundColor Green
        
        # Clean up
        $graphics.Dispose()
        $bitmap.Dispose()
        $originalImage.Dispose()
        $brush.Dispose()
        $path.Dispose()
        
    } catch {
        Write-Host "❌ Error creating favicon: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Define paths
$logoPath = "C:\Users\Abdal\OneDrive\Desktop\PangAIaShop-BackEnd\public\admin-assets\img\logo1.png"
$publicPath = "C:\Users\Abdal\OneDrive\Desktop\PangAIaShop-BackEnd\public"

# Check if logo exists
if (-not (Test-Path $logoPath)) {
    Write-Host "❌ Logo file not found: $logoPath" -ForegroundColor Red
    exit 1
}

Write-Host "🎨 Creating rounded favicons from logo1.png..." -ForegroundColor Cyan

# Create different sizes for optimal compatibility
$sizes = @(
    @{Size=16; Radius=8; Name="favicon-16x16"},
    @{Size=32; Radius=16; Name="favicon-32x32"},
    @{Size=48; Radius=24; Name="favicon-48x48"},
    @{Size=64; Radius=32; Name="favicon-64x64"},
    @{Size=128; Radius=64; Name="favicon-128x128"},
    @{Size=256; Radius=128; Name="favicon-256x256"}
)

foreach ($sizeInfo in $sizes) {
    $outputPath = Join-Path $publicPath "$($sizeInfo.Name).png"
    Create-RoundedFavicon -InputPath $logoPath -OutputPath $outputPath -Size $sizeInfo.Size -CornerRadius $sizeInfo.Radius
}

# Create the main favicon files
Write-Host "`n🔄 Creating main favicon files..." -ForegroundColor Cyan

# Create rounded favicon.png (32x32 for main public folder)
Create-RoundedFavicon -InputPath $logoPath -OutputPath "$publicPath\favicon.png" -Size 32 -CornerRadius 16

# Create for assets/img (frontend)
$frontendFaviconPath = "$publicPath\assets\img\favicon.png"
Create-RoundedFavicon -InputPath $logoPath -OutputPath $frontendFaviconPath -Size 32 -CornerRadius 16

# Create admin-specific rounded favicon
$adminFaviconPath = "$publicPath\admin-assets\img\admin-favicon.png"
Create-RoundedFavicon -InputPath $logoPath -OutputPath $adminFaviconPath -Size 32 -CornerRadius 16

Write-Host "`n✅ Rounded favicon generation complete!" -ForegroundColor Green
Write-Host "📁 Files created in: $publicPath" -ForegroundColor Yellow
Write-Host "🌐 Frontend favicon: $frontendFaviconPath" -ForegroundColor Yellow
Write-Host "⚙️ Admin favicon: $adminFaviconPath" -ForegroundColor Yellow

Write-Host "`n📝 Next steps:" -ForegroundColor Cyan
Write-Host "1. The script has created rounded versions of your favicon" -ForegroundColor White
Write-Host "2. Update your HTML templates to use the new favicon files" -ForegroundColor White
Write-Host "3. Clear browser cache to see the changes" -ForegroundColor White

# Convert PNG to ICO for better Windows compatibility
Write-Host "`n🔄 Attempting to create ICO file..." -ForegroundColor Cyan
try {
    # Try to create ICO using ImageMagick if available
    $magickPath = Get-Command "magick" -ErrorAction SilentlyContinue
    if ($magickPath) {
        & magick convert "$publicPath\favicon-32x32.png" "$publicPath\favicon.ico"
        Write-Host "✅ Created favicon.ico using ImageMagick" -ForegroundColor Green
    } else {
        Write-Host "⚠️ ImageMagick not found. ICO file not created." -ForegroundColor Yellow
        Write-Host "   You can use the PNG files or install ImageMagick for ICO conversion." -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️ Could not create ICO file: $($_.Exception.Message)" -ForegroundColor Yellow
}
