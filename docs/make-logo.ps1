# docs/make-logo.ps1 : turn a campus logo (JPG/PNG on white) into the 8 website logo files,
# same pixel sizes as the SKANS logos (assets/img/skans-logo-*.png), white background made transparent.
#   powershell -ExecutionPolicy Bypass -File docs\make-logo.ps1 -Src logo.jpg -Out _release\golra\logo
param([Parameter(Mandatory)][string]$Src, [Parameter(Mandatory)][string]$Out)
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
Add-Type -ReferencedAssemblies System.Drawing -TypeDefinition @"
using System; using System.Drawing; using System.Drawing.Imaging; using System.Runtime.InteropServices;
public static class LogoTool {
  // White -> transparent (soft edge), then trim the empty border.
  public static Bitmap Clean(string path) {
    var src = new Bitmap(path);
    var bmp = new Bitmap(src.Width, src.Height, PixelFormat.Format32bppArgb);
    using (var g = Graphics.FromImage(bmp)) g.DrawImage(src, 0, 0, src.Width, src.Height);
    var r = new Rectangle(0, 0, bmp.Width, bmp.Height);
    var d = bmp.LockBits(r, ImageLockMode.ReadWrite, PixelFormat.Format32bppArgb);
    var px = new byte[d.Stride * d.Height]; Marshal.Copy(d.Scan0, px, 0, px.Length);
    int minX = bmp.Width, minY = bmp.Height, maxX = -1, maxY = -1;
    for (int y = 0; y < bmp.Height; y++) for (int x = 0; x < bmp.Width; x++) {
      int i = y * d.Stride + x * 4; int b = px[i], gg = px[i+1], rr = px[i+2];
      int light = Math.Min(rr, Math.Min(gg, b));
      int a = light >= 250 ? 0 : (light <= 200 ? 255 : (250 - light) * 255 / 50);
      if (a > 0 && a < 255) {            // un-blend from white so edges keep their colour
        double f = a / 255.0;
        px[i+2] = (byte)Math.Max(0, Math.Min(255, (rr - 255 * (1 - f)) / f));
        px[i+1] = (byte)Math.Max(0, Math.Min(255, (gg - 255 * (1 - f)) / f));
        px[i]   = (byte)Math.Max(0, Math.Min(255, (b  - 255 * (1 - f)) / f));
      }
      px[i+3] = (byte)a;
      if (a > 20) { if (x < minX) minX = x; if (x > maxX) maxX = x; if (y < minY) minY = y; if (y > maxY) maxY = y; }
    }
    Marshal.Copy(px, 0, d.Scan0, px.Length); bmp.UnlockBits(d); src.Dispose();
    var crop = new Rectangle(minX, minY, maxX - minX + 1, maxY - minY + 1);
    var outb = bmp.Clone(crop, PixelFormat.Format32bppArgb); bmp.Dispose(); return outb;
  }
}
"@
$logo = [LogoTool]::Clean((Resolve-Path $Src).Path)
New-Item -ItemType Directory -Force -Path $Out | Out-Null
$sizes = @{ 50 = 105; 60 = 126; 76 = 160; 100 = 210; 120 = 252; 152 = 319; 228 = 479; 640 = 640 }
foreach ($k in $sizes.Keys) {
  $W = $sizes[$k]; $H = if ($k -eq 640) { 305 } else { $k }
  $canvas = [System.Drawing.Bitmap]::new($W, $H, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
  $g = [System.Drawing.Graphics]::FromImage($canvas); $g.Clear([System.Drawing.Color]::Transparent)
  $g.InterpolationMode = 'HighQualityBicubic'; $g.SmoothingMode = 'HighQuality'; $g.PixelOffsetMode = 'HighQuality'
  $sc = [Math]::Min($W / $logo.Width, $H / $logo.Height); $w = [int]($logo.Width * $sc); $h = [int]($logo.Height * $sc)
  $g.DrawImage($logo, [int](($W - $w) / 2), [int](($H - $h) / 2), $w, $h)
  $canvas.Save((Join-Path $Out "skans-logo-$k.png"), [System.Drawing.Imaging.ImageFormat]::Png); $g.Dispose(); $canvas.Dispose()
  "skans-logo-$k.png  $W x $H"
}
$logo.Dispose()
