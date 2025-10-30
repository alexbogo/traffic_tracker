# GeoIP Database Setup

## About GeoIP in This Project

This project uses the **MaxMind GeoLite2 Country database** for IP geolocation.

### Important: We Use the PHP Library, Not PECL Extension

**We use `geoip2/geoip2` PHP library** (installed via Composer) instead of the PECL extension because:
- ✅ Compatible with PHP 8.2+
- ✅ Actively maintained by MaxMind
- ✅ Better API and more features
- ✅ No compilation required
- ✅ Works across all platforms

The old PECL `geoip` extension is **NOT compatible** with PHP 8.x and has been removed from our Dockerfile.

---

## How to Get the GeoLite2 Database

### Step 1: Sign Up for a Free Account

1. Go to: https://www.maxmind.com/en/geolite2/signup
2. Create a free account
3. Verify your email address

### Step 2: Generate a License Key

1. Log in to your MaxMind account
2. Go to: https://www.maxmind.com/en/accounts/current/license-key
3. Click "Generate new license key"
4. Give it a name (e.g., "Traffic Tracker Dev")
5. Select "No" for "Will this key be used for GeoIP Update?"
6. Save your license key (you'll need it in the next step)

### Step 3: Download the Database

#### Option A: Direct Download (Easiest)

1. Log in to MaxMind
2. Go to: https://www.maxmind.com/en/accounts/current/geoip/downloads
3. Find "GeoLite2 Country" in the list
4. Click "Download GZIP" (for the `.mmdb` format)
5. Extract the `.gz` file to get `GeoLite2-Country.mmdb`
6. Place it in: `/Volumes/AlexStuff/Yomali/traffic_tracker/docker/geoip/GeoLite2-Country.mmdb`

#### Option B: Using geoipupdate (Automatic Updates)

```bash
# Install geoipupdate (on macOS)
brew install geoipupdate

# Or download from: https://github.com/maxmind/geoipupdate/releases

# Configure it
mkdir -p ~/.maxmind
cat > ~/.maxmind/GeoIP.conf << EOF
AccountID YOUR_ACCOUNT_ID
LicenseKey YOUR_LICENSE_KEY
EditionIDs GeoLite2-Country
DatabaseDirectory /Volumes/AlexStuff/Yomali/traffic_tracker/docker/geoip
EOF

# Download the database
geoipupdate
```

Replace `YOUR_ACCOUNT_ID` and `YOUR_LICENSE_KEY` with your actual values.

---

## Verify the Database

After placing the file, verify it exists:

```bash
ls -lh /Volumes/AlexStuff/Yomali/traffic_tracker/docker/geoip/

# You should see:
# GeoLite2-Country.mmdb
```

The file should be around 5-7 MB.

---

## Using the Database in Code

The database will be mounted in the PHP container at:
```
/usr/share/GeoIP/GeoLite2-Country.mmdb
```

In your Symfony code, you'll use it like this:

```php
use GeoIp2\Database\Reader;

// The path is configured in .env as GEOIP_DATABASE_PATH
$reader = new Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb');

try {
    $record = $reader->country('128.101.101.101');
    echo $record->country->isoCode; // 'US'
    echo $record->country->name; // 'United States'
} catch (\GeoIp2\Exception\AddressNotFoundException $e) {
    // IP address not found in database
}
```

---

## What If I Don't Have the Database?

**The application will still work!** 

- ✅ All tracking features work
- ✅ Visits are logged
- ✅ Dashboard works
- ⚠️ Country information will be `NULL` in the database
- ⚠️ Country-based analytics won't show data

---

## Keeping the Database Updated

MaxMind updates the GeoLite2 databases weekly. To stay current:

1. **Manual Update:** Download new version from MaxMind website every month
2. **Automatic Update:** Use `geoipupdate` with a cron job

Example cron for weekly updates:
```bash
# Update GeoIP database every Monday at 2 AM
0 2 * * 1 /usr/local/bin/geoipupdate
```

---

## Database License

The GeoLite2 databases are provided by MaxMind under the Creative Commons Attribution-ShareAlike 4.0 International License.

This means:
- ✅ Free to use
- ✅ Can use commercially
- ✅ Must provide attribution to MaxMind
- ✅ Must share modifications under same license

**Attribution Text:**
```
This product includes GeoLite2 data created by MaxMind, 
available from https://www.maxmind.com
```

---

## Troubleshooting

### "Database file not found" error
- Verify the file exists at the correct path
- Check file permissions (should be readable by `www-data`)
- Restart Docker containers after adding the file

### "Cannot open database" error
- The file might be corrupted
- Re-download from MaxMind
- Ensure it's the `.mmdb` format, not `.gz`

### Permission issues
```bash
# Fix permissions
chmod 644 /Volumes/AlexStuff/Yomali/traffic_tracker/docker/geoip/GeoLite2-Country.mmdb
```

---

## Alternative: Using a Different Database

If you prefer a different GeoIP database:

1. Place your `.mmdb` file in `docker/geoip/`
2. Update the path in `.env`:
   ```
   GEOIP_DATABASE_PATH="/usr/share/GeoIP/your-database.mmdb"
   ```
3. Restart containers

---

**Need Help?** Check MaxMind's documentation: https://dev.maxmind.com/geoip/geolite2-free-geolocation-data
