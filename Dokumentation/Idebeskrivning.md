# Idébeskrivning för mashup


## Beskrivning
Min idé är att skapa en applikation som kan generera ut recept på vad man ska äta, beroende på vad man som användare tycker om och inte. Man ska kunna logga in på sitt konto, och där lagras vad man tycker om de olika recepten. När man först loggar in genereras recepten fritt utan någon bortsortering (om man inte valt att man är vegetarian eller vegan), och då kan man välja om man vill favorisera receptet, ogilla receptet eller bara generera fram ett nytt utan att tycka till. 
Om användaren ogillar receptet kommer det inte med igen vid generering av recept. De favoriserade recepten kan användaren visa i en lista på sin profil. Vid visning av ett recept kan användaren även välja att dela receptet på facebook.


## Apier
1. Säsongsmat
	- Hämta recept från deras databas
	- (Om tid finns) Lägga till recept till deras databas
2. Facebook dialog api
	- För att autentisiera användare
	- För att kunna dela recept smidigt


### Dataformat
Säsongsmat:  json, jsonfm, php, phpfm, wddx, wddxfm, xml, xmlfm, yaml, yamlfm, rawfm, txt, txtfm, dbg, dbgfm, dump, dumpfm
Facebook: Javascript


### Krav
Det finns inga krav från apierna så länge man håller sig under rimligt antal request per dag, vilket jag inte anser vara ett problem till den här applikationen.


### Risker
När jag läste dokumentationen till säsongsmat stod det att det var under konstruktion och att det förmodligen kommer att ändras i framtiden. Jag vet dock inte när detta skrevs eller om det faktiskt är aktuellt idag, men om det skulle vara är det en risk till att använda detta api. 

Facebooks api litar jag på eftersom facebook är ett stort företag och jag vet att många använder deras tjänster och apier.