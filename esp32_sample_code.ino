#include <WiFi.h>
#include <HTTPClient.h>
#include "DHT.h"

// ESP32 monitoring only:
// This code reads temperature and humidity and sends them to PHP.
// It does not control relays, bulbs, fans, or the HX-W3001.

const char* ssid = "YOUR_WIFI_NAME";
const char* password = "YOUR_WIFI_PASSWORD";

// Use your computer or laptop IPv4 address, not localhost.
const char* serverUrl = "http://192.168.1.12/egg-incubator/api/save_reading.php";
const char* apiKey = "your_secret_api_key";

#define DHTPIN 4
#define DHTTYPE DHT22

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(115200);
  dht.begin();

  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.print("Connected. ESP32 IP address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  float humidity = dht.readHumidity();
  float temperature = dht.readTemperature();

  if (isnan(humidity) || isnan(temperature)) {
    Serial.println("Failed to read from sensor");
    delay(10000);
    return;
  }

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-API-KEY", apiKey);

    String payload = String("{\"temperature\":") + String(temperature, 1) +
                     String(",\"humidity\":") + String(humidity, 1) +
                     String("}");

    int httpResponseCode = http.POST(payload);

    Serial.print("POST payload: ");
    Serial.println(payload);
    Serial.print("HTTP response code: ");
    Serial.println(httpResponseCode);
    Serial.print("Server response: ");
    Serial.println(http.getString());

    http.end();
  } else {
    Serial.println("WiFi disconnected");
  }

  delay(10000);
}
