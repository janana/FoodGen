# Rapport för kurs 1DV449 projekt 
## - FoodGen


[Länk till projektet](http://janinaeb.se/FoodGen)

(OBS: Måste logga in med facebook för att få tillgång till all funktionalitet)

[Länk till redovisnings-video](http://www.youtube.com/watch?v=ARbTCLKMQq4)


### Inledning
Nästan varje dag ställs jag inför ett jobbigt beslut - vad ska jag äta idag? Därför har jag alltid önskat ha någon som kan bestämma åt mig, helst någon som vet vad jag är sugen på. Men eftersom tekniken inte gått så pass långt än, så får det duga med en applikation man kan tala om vad man tycker om och inte för.

Min applikation tar recept från [säsongsmat.nu] och visar ett slumpat för användaren. Användaren kan därefter välja att dela receptet på facebook, favorisera receptet så det sparas i en lista på användarens profil, rata receptet så det inte tas med i slumpningen igen, eller slumpa fram ett nytt recept. På användarens profil kan favoriserade och ratade recept visas och hanteras, och användaren kan även ställa in en kostinställning om denne är vegetarian, vegan eller allätare.



Startsidan av applikationen, där man får valet att logga in via facebook längst upp i högra hörnet.


![Screencap på startsidan om man är utloggad](Screencaps/startsida-utloggad.tiff "Startsida, utloggad")


Hur receptsidor ser ut om man inte är inloggad. Alla recept har en unik URL, och man behöver inte vara inloggad för att visa dem. Man kan dock inte slumpa fram recept om man inte är inloggad. Man kan inte heller rata eller favorisera receptet, trots att knapparna fortfarande finns på sidan (Jag fick inte till funktionalitet för att testa om användaren är inloggad innan receptets HTML renderas, då facebooks api initieras asynkront, och sidan blir mycket långsammare att ladda annars.). Vid klick på favorisera- eller rata-knapparna kommer en meddelanderuta upp som ber användaren att logga in via facebook för att kunna använda funktionaliteten. Att kunna dela receptet kan användaren fortfarande göra, trots att den inte är inloggad.


![Screencap på receptsida om man är utloggad](Screencaps/recept-utloggad.tiff "Receptsida, utloggad")


Hur receptsidor ser ut om man är inloggad. Funktionalitet för att slumpa fram nytt recept, och gå in på användarens profil tillkommer uppe i menyraden. Användaren har möjlighet att kunna rata, favorisera och dela receptet på facebook.


![Screencap på recept om man är inloggad](Screencaps/random-recept.tiff "Receptsida, inloggad")


Hur användarens profil ser ut. Användaren har möjlighet att gå in och hantera sina favoritrecept och de ratade recepten, samt välja kost-inställning och att logga ut från facebook och applikationen.


![Screencap på profilsidan om man är inloggad](Screencaps/profil.tiff "Profilsidan")


Hur användarens hantering av favoriserade recept ser ut. Användaren har möjlighet att gå in på receptet via länken och att ta bort favoriseringen genom att trycka på krysset.


![Screencap på profilsidans hantering av favoriserade recept](Screencaps/hantera-favoritrecept.tiff "Profilsidans hantering av favoritrecept")


Hur användarens hantering av ratade recept ser ut. Användaren har samma möjlighet som vid de favoriserade recepten att gå in på receptet via länken och att ta bort ratningen genom att trycka på krysset.


![Screencap på profilsidans hantering av ratade recept](Screencaps/hantera-ratade-recept.tiff "profilsidans hantering av ratade recept")



### Serversida
#### Språk
PHP, databas: MySQLi 

#### Funktionalitet och felhantering
Används för att hämta och skriva data om användare och recept till databasen. Data hämtas med ajax-anrop som JSON, eller text vid saknaden av objekt att returnera. Try/catch-satser används för att hantera fel gällande databasen.

#### Cachning
Det går inte att cacha filer på serversidan, då de främst används till att generera fram recept ur en lista. Om filerna cachas hämtas samma recept hela tiden.



### Klientsida
#### Språk
HTML, CSS och Javascript. 

#### Ramverk
Bootstrap och jQuery.

#### APIer
Facebooks api för inloggning och delning av recept. 

Receptinformationen skrapas från [säsongsmats hemsida](säsongsmat.nu), från kategorierna Varmrätter, Förrätter och smårätter, Soppor och Sallader. De har ett api för att hämta recept, men hur jag än gjorde fick jag ändå inte ut all information jag behövde från det. Dessutom skulle jag behövt göra 2-3 förfrågningar till deras api per recept, och eftersom det finns ganska många recept (~120 st just nu i kategorierna jag hämtar ut) tyckte jag att skrapning kändes bättre. Det var inte lätt att skrapa deras hemsida eftersom den är helt ostrukturerad, och efter mycket krångel finns det fortfarande information som inte kommer med på några få recept. 

#### Cachning
Jag har satt, i .htaccess-filen, att css cachas i 30 dagar efter att filen senast blev ändrad (Enl bineros cache-hjälp). Bilder är satta till 90 dagar. 

```
ExpiresActive On
ExpiresByType text/css M2592000
ExpiresByType image/jpg M7776000
ExpiresByType image/png M7776000
```


HTML och text bör inte cachas då HTML-sidan ändras dynamiskt hela tiden, speciellt när man genererar recept. En text-fil används för att lagra senaste datum recept-databasen uppdaterades, så därför kan inte den cachas då den ändras varje dag någon besöker hemsidan. Javascript kan inte heller cachas, då en användare kan ändra sina inställningar när den använder sidan. Om javascript-filen cachas kan inte användaren byta kost, då den fastnar på samma som tidigare.

#### Felhantering
Fel från serversidan hanteras via strängar, vilket skapar många strängberoenden som inte är speciellt bra men som fungerar i applikationen som den ser ut nu. Efter ett serveranrop kontrolleras utdatan om den är fel, och annars tas den omhand som ett objekt som är förväntat av applikationen. Generella fel- och rättmeddelanden visas efter utförd funktion via bootstraps alert-rutor.


### Egen reflektion
Jag har haft väldigt mycket problem med att få ut information om recepten. De första veckorna i projektet spenderades i maildiskussion med skaparen av säsongsmat för att få reda på hur man använder deras api för att få ut recept. Till slut bestämde jag mig för att strunta i apiet och skrapa deras receptsidor istället, och efter det har nästan allt flytit på. 


Jag har även haft problem med text-formatet vid skrapning av information, då det på vissa recept funnits konstiga tecken, vilket John hjälpte mig med på handledningen.


Utöver detta har mitt arbete flytit på bra, men jag skulle ha velat hunnit klart tidigare än jag gjort. Då skulle jag ha gett mig på att lägga in funktionalitet för att lägga till egna recept till säsongsmat från min applikation. Det är något jag skulle tänka mig lägga till i framtiden. 


### Risker med applikationen
Att alla recept inte är fullständiga. Om man hittar ett recept man verkligen vill göra finns det en risk att det endast finns knappa instruktioner, eller att några ingredienser inte står med. Användare kan antingen bli besvikna på min applikation, och/eller lämnar den och går till originalsidan istället.


En risk är om säsongsmat skulle ändra strukturen på deras receptsidor. I så fall skulle kanske min skrapningsfunktionalitet inte fungera som planerat och receptinformationen kan blir helt fel. Jag valde att använda säsongsmat för recept-hämtning från första början eftersom de delar med sig av all information och släpper den fritt, vilket är riktigt bra. Etiskt finns då i alla fall ingen risk med att använda deras data.

