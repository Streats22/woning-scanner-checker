<?php

return [
    'rec_high_1' => 'Stuur geen geld of persoonsgegevens voordat je de woning en verhuurder hebt geverifieerd.',
    'rec_high_2' => 'Vraag om een videocall of fysieke bezichtiging via een betrouwbaar kanaal (niet alleen WhatsApp).',
    'rec_mid_1' => 'Wees extra kritisch: controleer adres, KvK/identiteit van de verhuurder en zoek de advertentie elders terug.',
    'rec_low_1' => 'Blijf voorzichtig: controleer altijd sleuteloverdracht en contract voordat je betaalt.',
    'check_url_1' => 'Vergelijk de advertentie met dezelfde woning op Funda, Pararius of de site van een erkende makelaar.',
    'check_url_2' => 'Controleer of het domein van de link bij een bekende verhuurplatform past (let op typfouten in de URL).',
    'check_no_url_1' => 'Zoek een deel van de advertentietekst online — scams kopiëren vaak teksten.',
    'check_chat_apps' => 'Vraag een vast telefoonnummer of e-mail van een bedrijfsdomein; wees wantrouwig bij alleen chat-apps zonder traceerbaar kanaal.',
    'check_price_compare' => 'Vergelijk de prijs met vergelijkbare woningen in dezelfde buurt (minimaal 3 referenties).',
    'check_room_price_extreme' => 'Vergelijk deze kamerprijs met vergelijkbare kamers in dezelfde buurt (minimaal 3 referenties) — een grote afwijking t.o.v. een hele-woning-benchmark is voor kamers vaak normaal.',
    'check_no_wu' => 'Betaal nooit voor een “reservering” via Western Union, giftcards of crypto.',
    'check_id_landlord' => 'Vraag schriftelijk om legitimatie (ID) en bewijs van verhuurrecht voordat je een storting doet.',
    'hint_room' => 'Bij een kamer: check huisgenoten en huisregels, en vraag schriftelijk wat er in de huur is inbegrepen (servicekosten, internet).',
    'hint_social' => 'Bij sociale huur: controleer of de aanbieder echt een woningcorporatie of erkend toewijzingskanaal is; let op phishing met vervalste “Woonnet”-links.',
    'hint_private' => 'Bij particuliere verhuur: controleer eigendom (Kadaster) en identiteit van de verhuurder; wees extra alert op vooruitbetaling zonder bezichtiging.',
    'room_benchmark_note' => 'Voor een kamer is een veel lagere huur dan dit model (gemiddelde hele woning in de gemeente) gebruikelijk; dat zegt op zich weinig over betrouwbaarheid. ',
    'market_footer' => 'De benchmark is een vereenvoudigde schatting per stad/regio en geen taxatierapport.',
    'market_diff_suffix' => '; afwijking t.o.v. jouw prijs: :pct%.',
    'market_city_line' => 'Benchmark huur (model): €:avg/maand:suffix ',
    'methodology' => <<<'MD'
Stap 1 — invoer: uit je plaktekst of URL halen we prijs, contact en beschrijving (geen “geheime” bronnen). Zo mogelijk lezen we ook oppervlakte (m²) uit de tekst.

Stap 1b — type woning: we lezen de tekst en link en schatten of het om een kamer of een hele woning/studio gaat, en of het waarschijnlijk particuliere huur, sociale huur of onbekend is. Dat is een automatische inschatting op veelvoorkomende woorden en URL-patronen — geen juridische kwalificatie.

Stap 1c (informatief): we geven apart aan hoe sterk de tekst op een huuradvertentie lijkt — dat wijzigt de risicoscore niet.

Stap 2 — stad & benchmark: we proberen een gemeente te herkennen (tekst en URL-pad; o.a. komma-regels zoals “straat, plaats”) en vergelijken met een vaste model-benchmark (€/maand) per gemeente; elders geldt een standaard. Dat is een grove schatting, geen taxatie.

Stap 2b — prijs per m² (alleen als prijs én m² uit de tekst komen): we tonen een indicatieve €/m² naast het model en een band die voor kleine oppervlaktes hoger mag zijn — nog steeds geen taxatie.

Stap 3 — regels: de regel-score telt meetbare signalen op (max. 100):

Prijs & urgentie
– prijs ruim onder benchmark +30 (voor een kamer: alleen bij opvallend laag t.o.v. hetzelfde model, want kamers liggen normaal veel onder een gemiddelde voor een hele woning)
– WhatsApp +10
– Telegram/Signal/WeChat/Skype +8
– urgentie (o.a. “vandaag”, “snel”, “beperkte tijd”, “veel interesse”) +20

Betaling, bezichtiging & vertrouwen
– hoog-risico betalen (Western Union, crypto, cadeaukaarten, vooruitbetaling, enz.) +40
– geen bezichtiging / buitenland / sleutel per post of sleutelservice +12
– identiteit/privacy: o.a. ID vóór afspraak +10; “ID uitwisselen”-patroon +10 (elk kan afzonderlijk meetellen)
– geld vóór bezichtiging +14

Formulieren & taal
– extern aanmeldformulier (Google Forms, Typeform, …) +10
– klassiek emotioneel buitenland-verhaal +10
– Engelstalige scam-sjabloonzinnen +8

Stap 4 — optioneel AI: tweede risicoscore; eindscore = maximum van regel-score en AI-score. Zonder AI blijft de eindscore gelijk aan de regel-score.

Dit is geen juridisch of financieel advies en geen garantie tegen fraude.
MD,
];
