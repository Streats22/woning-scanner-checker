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
    'check_legal_costs' => 'De borg mag maximaal :max maanden kale huur zijn en bemiddelings- of contractkosten mogen niet bij jou als huurder in rekening worden gebracht (Wet goed verhuurderschap). Vraag een schriftelijk huurcontract met puntenoverzicht.',
    'check_energy_label_missing' => 'Er staat geen energielabel in de advertentie, terwijl een geldig label verplicht is bij verhuur en in de advertentie hoort. Vraag ernaar en controleer het op ep-online.nl.',
    'check_energy_label_poor' => 'Energielabel :label levert in het woningwaarderingsstelsel :points punten op. Daardoor kan de woning onder de middenhuur vallen en een lagere maximale huur hebben; check dit met de huurprijscheck van de Huurcommissie. Voor huurwoningen met E, F of G geldt naar verwachting vanaf :year een minimumnorm van label D.',
    'check_regulated_rent' => 'De huur valt rond of onder de wettelijke grenzen (2026: sociale huur t/m €:social, middenhuur t/m €:mid bij 186 punten). Dan bepaalt de puntentelling de maximale huur; doe de huurprijscheck van de Huurcommissie voor je tekent.',
    'check_temporary_contract' => 'Er wordt een tijdelijk contract genoemd. Sinds 1 juli 2024 zijn tijdelijke huurcontracten alleen in uitzonderingen toegestaan (o.a. studenten, wisselwoning); vraag welke uitzondering geldt.',
    'hint_room' => 'Bij een kamer: check huisgenoten en huisregels, en vraag schriftelijk wat er in de huur is inbegrepen (servicekosten, internet).',
    'hint_social' => 'Bij sociale huur: controleer of de aanbieder echt een woningcorporatie of erkend toewijzingskanaal is; let op phishing met vervalste “Woonnet”-links.',
    'hint_private' => 'Bij particuliere verhuur: controleer eigendom (Kadaster) en identiteit van de verhuurder; wees extra alert op vooruitbetaling zonder bezichtiging.',
    'room_benchmark_note' => 'Voor een kamer is een veel lagere huur dan dit model (gemiddelde hele woning in de gemeente) gebruikelijk; dat zegt op zich weinig over betrouwbaarheid. ',
    'market_room_line' => 'Mediane kamerhuur in deze gemeente: €:avg/maand (Kamernet 2026). ',
    'energy_label_note' => 'Energielabel :label: telt positief mee in de puntentelling; in de vrije sector heeft het label volgens onderzoek geen structureel effect op de vraaghuur. ',
    'energy_label_note_poor' => 'Energielabel :label: geeft aftrekpunten in de puntentelling, wat de maximale huur kan verlagen; in de vrije sector zegt het label weinig over de vraaghuur. ',
    'market_footer' => 'De benchmark is een vereenvoudigde schatting per gemeente op basis van Pararius (Q3 2026) en geen taxatierapport.',
    'market_diff_suffix' => '; afwijking t.o.v. jouw prijs: :pct%.',
    'market_city_line' => 'Benchmark huur (model): €:avg/maand:suffix ',
    'methodology' => <<<'MD'
Stap 1 — invoer: uit je plaktekst of URL halen we prijs, contact en beschrijving (geen “geheime” bronnen). Zo mogelijk lezen we ook oppervlakte (m²) uit de tekst.

Stap 1b — type woning: we lezen de tekst en link en schatten of het om een kamer of een hele woning/studio gaat, en of het waarschijnlijk particuliere huur, sociale huur of onbekend is. Dat is een automatische inschatting op veelvoorkomende woorden en URL-patronen — geen juridische kwalificatie.

Stap 1c (informatief): we geven apart aan hoe sterk de tekst op een huuradvertentie lijkt — dat wijzigt de risicoscore niet.

Stap 2 — stad & benchmark: we proberen een gemeente te herkennen (tekst en URL-pad; o.a. komma-regels zoals “straat, plaats”) en vergelijken met een model-benchmark per gemeente: de gemiddelde vraaghuur per m² in de vrije sector (Pararius Huurmonitor Q3 2026) × 65 m². Voor kamers gebruiken we de mediane kamerhuur (Kamernet 2026). Zonder herkende gemeente geldt het landelijke gemiddelde. Dat is een grove schatting, geen taxatie.

Stap 2b — prijs per m² (alleen als prijs én m² uit de tekst komen): we vergelijken de €/m² met de benchmark, met een hogere band voor kleine oppervlaktes — nog steeds geen taxatie.

Stap 2c — regels 2026 (informatief, telt niet mee in de score): we lezen het energielabel uit de tekst en wijzen op de puntentelling (Wet betaalbare huur), de maximale borg, verboden bemiddelingskosten en de regels voor tijdelijke contracten.

Stap 3 — regels: de regel-score telt meetbare signalen op (max. 100):

Prijs & urgentie
– prijs ruim onder benchmark +30 (met m²: €/m² minstens 45% onder de benchmark; zonder m²: onder 70% van het model en onder de sociale huurgrens; kamer: onder 55% van de mediane kamerhuur; sociale huur telt niet mee)
– WhatsApp +10
– Telegram/Signal/WeChat/Skype +8
– DM of “link in bio” (social-media-advertentie) +6
– urgentie (o.a. “vandaag”, “snel”, “beperkte tijd”, “veel interesse”, “andere kandidaten”) +20

Betaling, bezichtiging & vertrouwen
– hoog-risico betalen (Western Union, crypto, cadeaukaarten, vooruitbetaling, enz.) +40
– betalen “via Airbnb/Booking” of een platform-betaallink +35
– buitenlandse rekening of IBAN +20
– geen bezichtiging / buitenland / sleutel per post of sleutelservice +12
– identiteit/privacy: o.a. ID voor afspraak +10; “ID uitwisselen”-patroon +10 (elk kan afzonderlijk meetellen)
– geld voor bezichtiging, bezichtigings- of reserveringskosten, “eerst betalen” +14
– borg hoger dan 2 maanden huur +20; bemiddelings- of contractkosten voor de huurder +10
– verzoek om betaal- of accountgegevens te “verifiëren” +15

Formulieren & taal
– extern aanmeldformulier (Google Forms, Typeform, …) +10
– klassiek emotioneel buitenland-verhaal +10
– Engelstalige scam-sjabloonzinnen +8

Stap 4 — optioneel AI: tweede risicoscore; eindscore = maximum van regel-score en AI-score. Zonder AI blijft de eindscore gelijk aan de regel-score.

Dit is geen juridisch of financieel advies en geen garantie tegen fraude.
MD,
];
