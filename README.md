# Smart Egg Incubator Monitoring and Egg Batch Management System

Plain PHP, MySQL, HTML, CSS, and JavaScript project for XAMPP.

The ESP32 only sends temperature and humidity readings to PHP. The HX-W3001 handles the actual incubator temperature control.

## Local XAMPP Setup

1. Put this folder here:

   `C:/xampp/htdocs/egg-incubator/`

2. Open XAMPP Control Panel.

3. Start:

   - Apache
   - MySQL

4. Open the app in your browser:

   `http://localhost/egg-incubator/`

5. If your MySQL username or password is different, edit:

   `api/db.php`

   Default XAMPP values are:

   - host: `localhost`
   - database: `egg_incubator_db`
   - user: `root`
   - password: empty

## phpMyAdmin Database Import

1. Open:

   `http://localhost/phpmyadmin/`

2. Click `Import`.

3. Choose:

   `database.sql`

4. Click `Go`.

The SQL file creates the database `egg_incubator_db` and these tables:

- `readings`
- `settings`
- `egg_batches`

It also inserts the default warning settings:

- Lowest safe temperature: 35 C
- Highest safe temperature: 39 C
- Lowest safe humidity: 50%
- Highest safe humidity: 65%

## Main Pages

- `dashboard.php` shows the current temperature, humidity, alerts, tray status, and recent readings.
- `readings.php` lists saved sensor readings.
- `settings.php` updates dashboard warning ranges only.
- `trays.php` shows Egg Tray 1 and Egg Tray 2.
- `egg_batches.php` lists all egg batch records.
- `add_batch.php` adds a new Incubating batch.
- `edit_batch.php` edits a batch and recalculates the hatch date when needed.
- `view_batch.php` shows full batch details.

## ESP32 API URL

Use the computer or laptop IPv4 address, not localhost.

Example:

`http://192.168.1.12/egg-incubator/api/save_reading.php`

To find your IPv4 address on Windows:

1. Open Command Prompt.
2. Run `ipconfig`.
3. Look for `IPv4 Address` under your Wi-Fi or Ethernet adapter.

## API Key

The local development API key is stored in:

`api/api_auth.php`

Default:

`your_secret_api_key`

The ESP32 must send this HTTP header:

`X-API-KEY: your_secret_api_key`

## Postman API Testing

### Save a Reading

Method:

`POST`

URL:

`http://localhost/egg-incubator/api/save_reading.php`

Headers:

- `Content-Type: application/json`
- `X-API-KEY: your_secret_api_key`

Body, raw JSON:

```json
{
  "temperature": 37.5,
  "humidity": 60.2
}
```

Expected success response:

```json
{
  "success": true,
  "message": "Reading saved successfully"
}
```

If the API key is missing or invalid:

```json
{
  "success": false,
  "message": "Invalid API key"
}
```

### Get Latest Reading

Method:

`GET`

URL:

`http://localhost/egg-incubator/api/get_latest_reading.php`

### Get Recent Readings

Method:

`GET`

URL:

`http://localhost/egg-incubator/api/get_readings.php?limit=20`

### Get Warning Settings

Method:

`GET`

URL:

`http://localhost/egg-incubator/api/get_settings.php`

## ESP32 Arduino Code

Open:

`esp32_sample_code.ino`

Before uploading:

1. Change `YOUR_WIFI_NAME`.
2. Change `YOUR_WIFI_PASSWORD`.
3. Change `serverUrl` to your computer IPv4 address.
4. Keep the API key the same as `api/api_auth.php`.

Required Arduino libraries:

- WiFi
- HTTPClient
- DHT sensor library

The sample reads a DHT22 sensor and sends JSON to PHP every 10 seconds. It does not include relay, fan, bulb, HX-W3001, or temperature control logic.
