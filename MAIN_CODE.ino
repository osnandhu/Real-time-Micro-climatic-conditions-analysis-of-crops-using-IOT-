/* In arduino IDE, Go to File-> Preferences and in additional boards manager URL include:http://arduino.esp8266.com/stable/package_esp8266com_index.json.
Library to be added: adafruit_sensor.h
XAMPP:
Before starting Xampp quit Skype as both run in the same port. Disable  IIS. Now start XAMPP.
Go to XAMPP->htdocs and include the rest.api folder.
DATABASE CREATION:
Go to localhost:80->phpmyAdmin and create a new project and a table which will store the contents such as time,moisture,temperature and humidity.
Use POSTMAN app to check the Apis.*/

#include <DHT.h>
#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266WebServer.h>

#define DHTTYPE DHT11
#define DHTPIN  2
 
const char* ssid     = "xxxxxxx";//your router's ssid 
const char* password = "xxxxxxx";//your password
IPAddress host(192,168,xx,xx);//your laptop's IP address

WiFiClient client;


 
ESP8266WebServer server(80);
 
DHT dht(DHTPIN, DHTTYPE, 11); 
 
const int id=1;
int hum=0;
int temp=0;
int moisture = 0; 
String data="";     
unsigned long previousMillis = 0; 
unsigned long currentMillis = 0;      
const long interval = 2000;             

 
void setup(void)
{
  
  Serial.begin(9600);  
  dht.begin();           
  WiFi.begin(ssid, password);
  Serial.print("\n\r \n\rWorking to connect");
 
  // Wait for connection
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.println("DHT Weather Reading Server");
  Serial.print("Connected to ");
  Serial.println(ssid);
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  server.begin();
  Serial.println("HTTP server started");
}

void loop()
{
  currentMillis = millis();
  
  if(currentMillis - previousMillis > interval) 
  { // READ ONLY ONCE PER INTERVAL
    previousMillis = currentMillis;

    hum=dht.readHumidity();          // Read humidity (percent)
    Serial.println(hum);
    temp=dht.readTemperature();    
    Serial.println(temp); 
  }
  moisture=analogRead(A0);// to read the moisture values

 data = "id="+ String(id) + "&temp="+ String(int(temp)) + "&hum="+String(int(hum)) + "&moisture="+String(moisture);
 //Serial.println(data);
 
if (client.connect(host,80))
{
                    
                    client.print("POST /esp_dht/rest/sensor_insert HTTP/1.1\n");// the rest api file wh
                    client.print("Host: 192.168.xx.xx\n");
                    client.print("Connection: close\n");
                    client.print("Content-Type: application/x-www-form-urlencoded\n");
                    client.print("Content-Length: ");
                    client.print(data.length());
                    client.print("\n\n");
                    client.print(data);
                    Serial.print(data); 
                    Serial.println("\t success"); 
                    Serial.println("ARDUINO: HTTP message sent");
                    delay(60000);
      if(client.available())
      {
        Serial.println("ARDUINO: HTTP message received");
        Serial.println("ARDUINO: printing received headers and script response...\n");
        
        while(client.available())
        {
          char c = client.read();
          Serial.print(c);
        }
      }
      else
      {
        Serial.println("ARDUINO: no response received / no response received in time");
      }
                    

client.stop();
}
    else
    {
      Serial.println("connection failure");
    }
    delay(2000);

}
