"""
docs/prepare-campus.py : turn an OLD campus website backup into a READY-TO-UPLOAD zip of the new website.

    python docs/prepare-campus.py --backup "C:/path/chakri-backup.zip" --campus chakri \
        --domain chakricampus.skansschool.edu.pk --name "Chakri Road Campus"

What it does (nothing on the server, nothing in the backup is changed):
  1. Unpacks the backup into _release/<campus>/work/old/ (a folder backup also works).
  2. Copies the new website code (no AECHS pictures/video, no passwords) to _release/<campus>/work/site/.
  3. Finds the campus's own pictures and Home video on the old pages and puts them in the right
     places with the right names, resized for the web (Windows' own image library, nothing to install).
  4. Copies the old site's uploaded pictures (adminpanel/upload) and student photos (uploads).
  5. Writes campus-data/sites.php and campus-data/<campus>/config.php: database login from the old
     adminpanel/db.php (never printed), details found on the old pages (address, phones, email, links),
     the shared email login from the local AECHS config, and the campus switches (features).
  6. Makes _release/<campus>/<campus>-ready.zip with server permissions 0644 files / 0755 folders.
  7. Writes _release/<campus>/REPORT.txt: every picture used, every detail found, and what to check.
The zip holds passwords: keep it private, delete it from the server after extracting.
"""
import argparse, json, os, re, shutil, stat, subprocess, sys, zipfile, html
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent          # the project folder
SKIP_DIRS = {'_old', 'docs', 'backups', 'campus-data', '_release', '.git', 'node_modules', 'uploads'}
SKIP_SUB = ['adminpanel/upload']
AECHS_FILES = {'assets/video/hero.mp4', 'assets/video/hero-poster.jpg', 'assets/img/about-principal.jpg',
               'assets/img/about-campus-1.jpg', 'assets/img/about-campus-2.jpg', 'assets/img/about-campus-3.jpg',
               'assets/img/facilities-hero.jpg', 'assets/img/special-programs.jpeg',
               'assets/img/level-early-years.jpeg', 'assets/img/level-juniors.jpeg', 'assets/img/level-seniors.jpeg'}
KEEP = {'uploads/.htaccess', 'campus-data/.htaccess', 'campus-data/index.html'}
IMG_RE = re.compile(r'(?i)(?:src|poster)\s*=\s*["\']([^"\'<>?]+\.(?:jpe?g|png|webp|gif|mp4))')
BG_RE = re.compile(r'(?i)url\(\s*["\']?([^"\')]+\.(?:jpe?g|png|webp))')
report = []


def log(line=''):
    print(line)
    report.append(line)


def find_site_root(folder: Path) -> Path:
    """The folder that holds the old index.php (backups often have one extra folder level)."""
    hits = sorted(folder.rglob('index.php'), key=lambda p: len(p.parts))
    for p in hits:
        if (p.parent / 'adminpanel').is_dir() or (p.parent / 'cpenal').is_dir():
            return p.parent
    sys.exit('Could not find the old website (index.php + adminpanel) inside the backup.')


def old_admin(old: Path) -> Path:
    """The old site's admin folder: adminpanel (most campuses) or cpenal (Westridge EYE)."""
    for name in ('adminpanel', 'cpenal'):
        if (old / name).is_dir():
            return old / name
    return old / 'adminpanel'


def copy_new_code(dest: Path):
    for src in ROOT.rglob('*'):
        if not src.is_file():
            continue
        rel = src.relative_to(ROOT).as_posix()
        top = rel.split('/')[0]
        if rel in KEEP:
            pass
        elif top in SKIP_DIRS or any(rel == d or rel.startswith(d + '/') for d in SKIP_SUB):
            continue
        elif rel in AECHS_FILES or rel.startswith('downloads/skans-aechs-'):
            continue
        elif re.search(r'(^|/)zz_|\.(log|bak|zip)$|(^|/)error_log$', rel):
            continue
        (dest / rel).parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dest / rel)


def page_images(old: Path, page: str):
    f = old / page
    if not f.is_file():
        return []
    text = f.read_text(encoding='utf8', errors='replace')
    found = []
    for m in list(IMG_RE.finditer(text)) + list(BG_RE.finditer(text)):
        rel = html.unescape(m.group(1)).lstrip('./').replace('\\', '/')
        if rel.lower().startswith(('icons/', 'http', 'data:')) or 'logo' in rel.lower():
            continue
        p = old / rel
        if p.is_file() and rel not in [x[0] for x in found]:
            found.append((rel, p))
    return found


def image_size(path: Path):
    ps = ("Add-Type -AssemblyName System.Drawing; $i=[System.Drawing.Image]::FromFile($args[0]); "
          "Write-Output ($i.Width.ToString() + ' ' + $i.Height.ToString()); $i.Dispose()")
    try:
        out = subprocess.run(['powershell', '-NoProfile', '-Command', ps, str(path)], capture_output=True, text=True, timeout=60).stdout.split()
        return int(out[0]), int(out[1])
    except Exception:
        return None


def save_web(src: Path, dest: Path, max_side: int):
    """Resize to max_side and save as JPEG quality 84 (Windows' System.Drawing)."""
    ps = r"""
param($src, $dst, $max)
Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Image]::FromFile($src); $iw = $img.Width; $ih = $img.Height
$sc = [Math]::Min(1.0, [double]$max / [Math]::Max($iw, $ih)); $bw = [int]($iw * $sc); $bh = [int]($ih * $sc)
$bmp = [System.Drawing.Bitmap]::new($bw, $bh); $g = [System.Drawing.Graphics]::FromImage($bmp)
$g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$g.Clear([System.Drawing.Color]::White); $g.DrawImage($img, 0, 0, $bw, $bh)
$codec = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq 'image/jpeg' }
$ep = [System.Drawing.Imaging.EncoderParameters]::new(1)
$ep.Param[0] = [System.Drawing.Imaging.EncoderParameter]::new([System.Drawing.Imaging.Encoder]::Quality, [long]84)
$bmp.Save($dst, $codec, $ep); $g.Dispose(); $bmp.Dispose(); $img.Dispose()
Write-Output "$bw x $bh"
"""
    script = dest.parent / '_resize.ps1'
    script.write_text(ps, encoding='utf8')
    r = subprocess.run(['powershell', '-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', str(script), str(src), str(dest), str(max_side)],
                       capture_output=True, text=True, timeout=120)
    script.unlink()
    if r.returncode != 0 or not dest.is_file():
        raise RuntimeError(r.stderr.strip()[:300])
    return r.stdout.strip()


def pick_pictures(old: Path, site: Path):
    log('PICTURES AND VIDEO (old file -> new slot)')
    img, vid = site / 'assets/img', site / 'assets/video'
    img.mkdir(parents=True, exist_ok=True); vid.mkdir(parents=True, exist_ok=True)

    home = page_images(old, 'index.php')
    videos = [p for r, p in home if r.lower().endswith('.mp4')] or sorted((old / 'video').glob('*.mp4'))
    stills = [(r, p) for r, p in home if not r.lower().endswith('.mp4')]
    if videos:
        shutil.copy2(videos[0], vid / 'hero.mp4')
        mb = videos[0].stat().st_size / 1048576
        log(f'  assets/video/hero.mp4            <- {videos[0].relative_to(old).as_posix()} ({mb:.1f} MB){"  !! over 10 MB: slow on phones" if mb > 10 else ""}')
    else:
        log('  !! NO HOME VIDEO FOUND: put the campus video in assets/video/hero.mp4 by hand')
    posters = [p for p in (old / 'video').glob('*') if p.suffix.lower() in ('.jpg', '.jpeg', '.png')] if (old / 'video').is_dir() else []
    poster = posters[0] if posters else (stills[0][1] if stills else None)

    about = page_images(old, 'about.php')
    special = page_images(old, 'advanced-placement-program.php')
    others = page_images(old, 'departments.php') + page_images(old, 'contact-us.php') + page_images(old, 'admissions.php') + stills
    pool = about + special + others
    sizes = {}
    def dims(p):
        if p not in sizes:
            sizes[p] = image_size(p)
        return sizes[p]
    def landscape(c):
        d = dims(c[1]); return d and d[0] >= d[1] * 1.15
    def portrait(c):
        d = dims(c[1]); return d and d[1] >= d[0] * 1.15

    used = []
    def take(cands, test=None):
        for c in cands:
            if c[1] in used or (test and not test(c)):
                continue
            used.append(c[1]); return c
        return None

    slots = [
        ('assets/video/hero-poster.jpg', [('video poster', poster)] if poster else [], None, 1600),
        ('assets/img/about-campus-1.jpg', about + pool, landscape, 1600),
        ('assets/img/about-campus-2.jpg', about + pool, landscape, 1400),
        ('assets/img/about-campus-3.jpg', about + pool, landscape, 1400),
        ('assets/img/special-programs.jpeg', special + pool, landscape, 1400),
        ('assets/img/facilities-hero.jpg', pool, portrait, 1200),
    ]
    chosen = {}
    for slot, cands, test, size in slots:
        c = take(cands, test) or take(cands) or (('re-used', chosen.get('assets/video/hero-poster.jpg')) if chosen.get('assets/video/hero-poster.jpg') else None)
        if not c or not c[1]:
            log(f'  !! {slot:32} NOT FOUND: add a picture with this name by hand'); continue
        out = site / slot
        wh = save_web(c[1], out, size)
        chosen[slot] = c[1]
        src_name = c[1].relative_to(old).as_posix() if old in c[1].parents else str(c[1])
        log(f'  {slot:32} <- {src_name} ({wh}, {out.stat().st_size // 1024} KB)')
    # temporary principal photo + level-card fallbacks (re-use the photos chosen above)
    for slot, base, size in [('assets/img/about-principal.jpg', 'assets/video/hero-poster.jpg', 1200),
                             ('assets/img/level-early-years.jpeg', 'assets/video/hero-poster.jpg', 1200),
                             ('assets/img/level-juniors.jpeg', 'assets/img/about-campus-3.jpg', 1200),
                             ('assets/img/level-seniors.jpeg', 'assets/img/special-programs.jpeg', 1200)]:
        src = chosen.get(base) or next(iter(chosen.values()), None)
        if src:
            wh = save_web(src, site / slot, size)
            log(f'  {slot:32} <- same as {base} ({wh})')
    log('  (about-principal.jpg is temporary: upload the real photo in Admin > Settings > Principal\'s message)')


def copy_uploads(old: Path, site: Path):
    log(''); log('UPLOADED PICTURES (stay the same as on the old site)')
    up = old_admin(old) / 'upload'
    if up.is_dir():
        shutil.copytree(up, site / 'adminpanel' / 'upload', dirs_exist_ok=True)
        n = sum(1 for p in (site / 'adminpanel' / 'upload').rglob('*') if p.is_file())
        log(f'  adminpanel/upload/ copied: {n} files')
    else:
        log('  !! old adminpanel/upload/ not found')
    st = old / 'uploads'
    if st.is_dir():
        for p in st.rglob('*'):
            if p.is_file():
                (site / 'uploads' / p.relative_to(st)).parent.mkdir(parents=True, exist_ok=True)
                shutil.copy2(p, site / 'uploads' / p.relative_to(st))
        log('  uploads/ (student photos) copied')


def php_string(v: str) -> str:
    return "'" + v.replace('\\', '\\\\').replace("'", "\\'") + "'"


def read_db_login(old: Path):
    for cand in [old_admin(old) / 'db.php', old / 'db.php']:
        if cand.is_file():
            t = cand.read_text(encoding='utf8', errors='replace')
            vals = {}
            for key in ('host', 'dbname', 'username', 'password'):
                m = re.search(r'\$' + key + r'\s*=\s*([\'"])(.*?)\1\s*;', t)
                vals[key] = m.group(2) if m else ''
            if vals['dbname']:
                return vals, cand.relative_to(old).as_posix()
    return None, None


def find_details(old: Path):
    text = ''
    for page in ['contact-us.php', 'footer.php', 'header.php', 'index.php', 'about.php']:
        f = old / page
        if f.is_file():
            text += '\n' + f.read_text(encoding='utf8', errors='replace')
    plain = html.unescape(re.sub(r'<[^>]+>', ' \n ', text))
    emails = [e for e in dict.fromkeys(re.findall(r'[\w.+-]+@[\w-]+\.[\w.-]+', plain)) if not e.lower().startswith(('noreply', 'example'))]
    phones = list(dict.fromkeys(m.strip() for m in re.findall(r'(?:\+?92[\s-]?|0)(?:3\d{2}|\d{2,3})[\s-]?\d{3,4}[\s-]?\d{3,4}', plain)))
    links = re.findall(r'href\s*=\s*["\']([^"\']+)["\']', text, re.I)
    fb = next((l for l in links if 'facebook.com' in l), '')
    ig = next((l for l in links if 'instagram.com' in l), '')
    maps = next((l for l in links if re.search(r'maps\.app\.goo\.gl|google\.[a-z.]+/maps|goo\.gl/maps|share\.google', l)), '')
    addr = ''
    m = re.search(r'(?is)Visit\s*Us\s*</h\d>(.{0,1500}?)(?:</a>|View\s*On\s*Map)', text)
    if m:                                  # all lines of the "Visit Us" block except a bare school name
        lines = [re.sub(r'\s+', ' ', html.unescape(re.sub(r'<[^>]+>', ' ', x))).strip(' ,')
                 for x in re.findall(r'(?is)<(?:p|address)[^>]*>(.*?)</(?:p|address)>', m.group(1))]
        lines = [l for l in lines if len(l) > 5 and not re.fullmatch(r'(?i)skans[\w\s]*?(school system|school)?\s*\.?', l)]
        lines = [re.sub(r'(?i)^skans[\w\s]*?(school system|school)\s*,\s*', '', l) for l in lines]
        addr = re.sub(r'\s+,', ',', ', '.join(l for l in lines if l))
    wa = next((re.sub(r'\D', '', l) for l in links if re.search(r'wa\.me/|api\.whatsapp\.com', l)), '')
    return {'whatsapp_link': wa, 'emails': emails, 'phones': phones, 'facebook': fb.strip(), 'instagram': ig.strip(), 'map': maps.strip(), 'address': addr}


def digits(phone: str) -> str:
    d = re.sub(r'\D', '', phone)
    return '92' + d[1:] if d.startswith('0') else d


def write_config(old: Path, site: Path, campus: str, domain: str, name: str, city: str, classes: str, school: str = 'SKANS School System'):
    log(''); log('CONFIG')
    cd = site / 'campus-data'
    (cd / campus).mkdir(parents=True, exist_ok=True)
    hosts = [domain] + ([] if domain.startswith('www.') else ['www.' + domain])
    (cd / 'sites.php').write_text("<?php\n// campus-data/sites.php : which website address shows which campus.\nreturn [\n"
                                  + ''.join(f"    {php_string(h)} => {php_string(campus)},\n" for h in hosts) + "];\n", encoding='utf8')
    log(f'  campus-data/sites.php: {", ".join(hosts)} -> {campus}')

    db, where = read_db_login(old)
    if db:
        log(f'  database: name {db["dbname"]}, user {db["username"]} (password copied, not shown) from old {where}')
    else:
        db = {'host': 'localhost', 'dbname': 'DATABASE_NAME', 'username': 'DATABASE_USER', 'password': 'DATABASE_PASSWORD'}
        log('  !! old db.php not found: fill in DATABASE_NAME / USER / PASSWORD by hand')
    mail = {'host': 'skansschool.edu.pk', 'port': 587, 'user': 'noreply@skansschool.edu.pk', 'pass': 'EMAIL_PASSWORD',
            'from': 'noreply@skansschool.edu.pk', 'from_name': 'SKANS', 'copy_to': ''}
    local = ROOT / 'campus-data' / 'aechs' / 'config.php'
    if local.is_file():
        try:
            out = subprocess.run([r'C:\xampp\php\php.exe', '-r', 'echo json_encode((require $argv[1])["mail"] ?? null);', str(local)],
                                 capture_output=True, text=True, timeout=30).stdout
            m = json.loads(out or 'null')
            if m:
                mail.update({k: v for k, v in m.items() if k != 'copy_to'})
                log('  email login: copied from the local AECHS config (check it is the CURRENT noreply@ password)')
        except Exception:
            pass

    d = find_details(old)
    email = next((e for e in d['emails'] if campus.lower() in e.lower()), d['emails'][0] if d['emails'] else 'CAMPUS EMAIL')
    def is_mobile(p):                      # Pakistani mobiles: 03xx / +92 3xx
        d = digits(p)
        return d.startswith('923')
    phones = d['phones']
    office = next((p for p in phones if not is_mobile(p)), '+92 51 0000000')
    mobile = next((p for p in phones if is_mobile(p)), '+92 300 0000000')
    w = d.get('whatsapp_link', '')
    if w.startswith('923'):                     # the old WhatsApp button's number wins
        mobile = next((p for p in phones if digits(p) == w), '+' + w[:2] + ' ' + w[2:5] + ' ' + w[5:])
    site_vals = {
        'name': school, 'campus': name, 'full_name': school + ', ' + name, 'city': city,
        'classes': classes, 'tagline': 'Education for life', 'address': d['address'] or 'CAMPUS ADDRESS',
        'map_url': d['map'] or '', 'email': email,
        'whatsapp': digits(mobile), 'facebook': d['facebook'], 'instagram': d['instagram'],
    }
    log(f'  found on the old pages: emails {d["emails"][:3]}, phones {d["phones"][:3]}')
    log(f'  address: {site_vals["address"]}')
    log(f'  map: {site_vals["map_url"] or "(none found)"}   facebook: {"yes" if d["facebook"] else "none"}   instagram: {"yes" if d["instagram"] else "none"}')

    cfg = ["<?php", f"// campus-data/{campus}/config.php : {name}. NEVER share this file, put it on GitHub or leave it in a zip on the server.",
           "return [", "    'db' => [",
           f"        'host' => {php_string(db['host'] or 'localhost')},", f"        'name' => {php_string(db['dbname'])},",
           f"        'user' => {php_string(db['username'])},", f"        'pass' => {php_string(db['password'])},", "    ],",
           "    'uploads' => 'adminpanel/upload/images',", "    'mail' => ["]
    for k in ('host', 'port', 'user', 'pass', 'from', 'from_name', 'copy_to'):
        v = mail[k]
        cfg.append(f"        {php_string(k)} => {v if isinstance(v, int) else php_string(str(v))},")
    cfg += ["    ],", "    // Campus switches (same choices as Chakri): anything left out stays ON.", "    'features' => [",
            "        'level_classes'           => false,   // class + age list on the 3 level cards",
            "        'level_board'             => false,   // \"Examination board: FBISE\" on the Seniors card",
            "        'academic_programs_admin' => false,   // Academic Programs in the admin menu and dashboard",
            "        // 'level_range'          => false,   // the line under each card title",
            "    ],", "    'site' => ["]
    for k in ('name', 'campus', 'full_name', 'city', 'classes', 'tagline', 'address', 'map_url', 'email'):
        cfg.append(f"        {php_string(k)} => {php_string(site_vals[k])},")
    cfg.append("        'phones' => [")
    cfg.append(f"            ['label' => 'Office', 'display' => {php_string(office)}, 'tel' => {php_string('+' + digits(office))}],")
    cfg.append(f"            ['label' => 'Mobile / WhatsApp', 'display' => {php_string(mobile)}, 'tel' => {php_string('+' + digits(mobile))}],")
    cfg.append("        ],")
    for k in ('whatsapp', 'facebook', 'instagram'):
        cfg.append(f"        {php_string(k)} => {php_string(site_vals[k])},")
    cfg += ["    ],", "];", ""]
    conf = cd / campus / 'config.php'
    conf.write_text('\n'.join(cfg), encoding='utf8')
    lint = subprocess.run([r'C:\xampp\php\php.exe', '-l', str(conf)], capture_output=True, text=True).stdout.strip()
    log(f'  campus-data/{campus}/config.php written; PHP check: {"OK" if "No syntax errors" in lint else "PROBLEM " + lint}')
    left = [w for w in ('DATABASE_', 'EMAIL_PASSWORD', 'CAMPUS ADDRESS', 'CAMPUS EMAIL', '0000000') if w in conf.read_text(encoding='utf8')]
    if left:
        log(f'  !! still to fill in by hand in config.php: {", ".join(left)}')


def make_zip(site: Path, out: Path):
    if out.exists():
        out.unlink()
    with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as z:
        for p in sorted(site.rglob('*')):
            rel = p.relative_to(site).as_posix()
            if p.is_dir():
                zi = zipfile.ZipInfo(rel + '/'); zi.create_system = 3; zi.external_attr = (0o40755 << 16) | 0x10
                z.writestr(zi, '')
            else:
                zi = zipfile.ZipInfo.from_file(p, rel); zi.create_system = 3; zi.external_attr = 0o100644 << 16
                zi.compress_type = zipfile.ZIP_STORED if p.suffix.lower() in ('.mp4', '.jpg', '.jpeg', '.png', '.webp', '.gif', '.zip') else zipfile.ZIP_DEFLATED
                with open(p, 'rb') as fh:
                    z.writestr(zi, fh.read())
    return out.stat().st_size / 1048576


def main():
    ap = argparse.ArgumentParser(description='Prepare a ready-to-upload zip for one campus.')
    ap.add_argument('--backup', required=True, help='old site backup: a .zip or an extracted folder')
    ap.add_argument('--campus', required=True, help='short lowercase id, e.g. chakri (folder name in campus-data)')
    ap.add_argument('--domain', required=True, help='web address without https://, e.g. chakricampus.skansschool.edu.pk')
    ap.add_argument('--name', required=True, help='campus name, e.g. "Chakri Road Campus"')
    ap.add_argument('--city', default='Rawalpindi')
    ap.add_argument('--school', default='SKANS School System', help='e.g. "SKANS International Islamic School"')
    ap.add_argument('--logo-dir', default='', help='folder with skans-logo-*.png made by docs/make-logo.ps1 (campus logo)')
    ap.add_argument('--classes', default='Playgroup – Matric', help='short classes line, e.g. "Playgroup – Class 8"')
    a = ap.parse_args()
    if not re.fullmatch(r'[a-z0-9-]{2,40}', a.campus):
        sys.exit('--campus must be lowercase letters, numbers or dashes')

    base = ROOT / '_release' / a.campus
    work = base / 'work'
    if work.exists():
        shutil.rmtree(work)
    (work / 'old').mkdir(parents=True); (work / 'site').mkdir()
    src = Path(a.backup)
    if not src.exists() and os.environ.get('SK_BACKUP'):     # paths with special characters (e.g. emoji) via env var
        src = Path(os.environ['SK_BACKUP'])
    log(f'Campus: {a.name} ({a.campus})   address: {a.domain}')
    log(f'Backup: {src}')
    if src.is_file() and src.suffix.lower() == '.zip':
        with zipfile.ZipFile(src) as z:
            for m in z.infolist():                       # skip nested backup zips and database folders
                n = m.filename.replace('\\', '/')
                if re.search(r'\.zip$|\.sql(\.gz)?$', n, re.I):
                    continue
                z.extract(m, work / 'old')
        old = find_site_root(work / 'old')
    elif src.is_dir():
        old = find_site_root(src)
    else:
        sys.exit('--backup must be a .zip file or a folder')
    log(f'Old site found at: {old}')
    log('')
    copy_new_code(work / 'site')
    pick_pictures(old, work / 'site')
    copy_uploads(old, work / 'site')
    write_config(old, work / 'site', a.campus, a.domain, a.name, a.city, a.classes, a.school)
    if a.logo_dir:
        n = 0
        for f in Path(a.logo_dir).glob('skans-logo-*.png'):
            shutil.copy2(f, work / 'site' / 'assets' / 'img' / f.name); n += 1
        log(f'  campus logo: {n} files from {a.logo_dir} (same names and sizes as the SKANS logo)')
    mb = make_zip(work / 'site', base / f'{a.campus}-ready.zip')
    log(''); log(f'READY: _release/{a.campus}/{a.campus}-ready.zip ({mb:.1f} MB). Files 0644, folders 0755.')
    log('Upload it into the EMPTY campus folder, Extract, delete the zip, then run the database steps.')
    (base / 'REPORT.txt').write_text('\n'.join(report) + '\n', encoding='utf8')


if __name__ == '__main__':
    main()
