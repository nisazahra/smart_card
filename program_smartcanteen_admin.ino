#include <SPI.h>
#include <MFRC522.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

#define RST_PIN D3
#define SS_PIN D4
MFRC522 rfid(SS_PIN, RST_PIN);


const char* ssid = "---";  // Ganti dengan SSID WiFi Anda
const char* password = "---";  // Ganti dengan password WiFi Anda
const char* serverURL = "http://192.168.213.98/web_admin/get_UID.php"; 
//Sesuaikan IP jaringan dan file yang dituju

void setup() {
  Serial.begin(115200);
  SPI.begin();
  rfid.PCD_Init();
  WiFi.begin(ssid, password);

  Serial.println("Menghubungkan ke WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nTerhubung ke WiFi!");
}

void loop() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    return;
  }

  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();  // Konversi UID ke uppercase

  // Kirim data uid_kartu
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    WiFiClient client;

    // Kirim ke server pertama
    http.begin(client, serverURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    String postData = "uid_kartu=" + uid;
    int httpResponseCode = http.POST(postData);
    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Response dari server: " + response);
      Serial.println("(Admin) UID kartu yang dikirim ke server: " + uid);
    } else {
      Serial.println("(Admin) Gagal mengirim data");
      Serial.print("HTTP Error: ");
      Serial.println(httpResponseCode);
    }
    http.end();  // Menutup koneksi HTTP

  } else {
    Serial.println("WiFi tidak terhubung");
  }

  delay(2000);  // Delay 2 detik sebelum membaca kartu berikutnya
}