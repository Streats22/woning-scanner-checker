/**
 * Gidspagina `/oplichting-woning-herkennen`: gericht op zoekvragen als “pas op scam woning”,
 * “oplichting woning” en “hoe check ik of een woningadvertentie betrouwbaar is”.
 * Inhoud per taal hier (lange, gestructureerde tekst); labels voor navigatie staan in i18n.
 * Bronnen: o.a. Fraudehelpdesk, Politie, Kadaster, Juridisch Loket, NOS (zie `sources`).
 */

export const SCAM_GUIDE_PATH = '/oplichting-woning-herkennen'

/** Bijwerken bij inhoudelijke wijziging; komt in Article-JSON-LD (dateModified). */
export const SCAM_GUIDE_PUBLISHED = '2026-10-27'
export const SCAM_GUIDE_MODIFIED = '2026-10-27'

export interface ScamGuideItem {
  title: string
  body: string
}

export interface ScamGuideFaq {
  q: string
  a: string
}

export interface ScamGuideContent {
  metaTitle: string
  metaDescription: string
  breadcrumb: string
  eyebrow: string
  title: string
  lead: string
  updatedLabel: string
  shortAnswerTitle: string
  shortAnswer: string[]
  ctaCheck: string
  ctaCheckNote: string
  signalsTitle: string
  signalsIntro: string
  signals: ScamGuideItem[]
  stepsTitle: string
  stepsIntro: string
  steps: ScamGuideItem[]
  typesTitle: string
  typesIntro: string
  types: ScamGuideItem[]
  paidTitle: string
  paidIntro: string
  paidSteps: string[]
  faqTitle: string
  faq: ScamGuideFaq[]
  sourcesTitle: string
  sourcesIntro: string
  ctaBottomTitle: string
  ctaBottomBody: string
  ctaFaq: string
  ctaSites: string
}

export const scamGuideSources: { title: string; titleEn: string; url: string }[] = [
  {
    title: 'Fraudehelpdesk: een dak boven je hoofd of van je centen beroofd?',
    titleEn: 'Fraudehelpdesk: rental fraud campaign (Dutch)',
    url: 'https://www.fraudehelpdesk.nl/campagnes/een-dak-boven-je-hoofd-of-van-je-centen-beroofd/',
  },
  {
    title: 'Fraudehelpdesk: fraude melden of advies vragen',
    titleEn: 'Fraudehelpdesk: report fraud or get advice',
    url: 'https://www.fraudehelpdesk.nl/',
  },
  {
    title: 'Politie: internetoplichting en aangifte doen',
    titleEn: 'Dutch police: report online fraud',
    url: 'https://www.politie.nl/onderwerpen/internetoplichting.html',
  },
  {
    title: 'Kadaster: eigenaar van een woning opzoeken',
    titleEn: 'Kadaster: look up who owns a property',
    url: 'https://www.kadaster.nl/producten/woning/eigendomsinformatie',
  },
  {
    title: 'KvK: bedrijf of makelaar opzoeken in het Handelsregister',
    titleEn: 'KvK: look up a company or agent in the trade register',
    url: 'https://www.kvk.nl/zoeken/',
  },
  {
    title: 'Rijksoverheid: KopieID-app (veilige kopie van je identiteitsbewijs)',
    titleEn: 'Dutch government: KopieID app (safe copy of your ID)',
    url: 'https://www.rijksoverheid.nl/onderwerpen/identiteitsfraude/vraag-en-antwoord/veilige-kopie-identiteitsbewijs',
  },
  {
    title: 'Juridisch Loket: huren en verhuren',
    titleEn: 'Juridisch Loket: renting (legal aid)',
    url: 'https://www.juridischloket.nl/alle-onderwerpen/huren/',
  },
  {
    title: 'Centraal Meldpunt Identiteitsfraude (RvIG)',
    titleEn: 'Central Identity Fraud Reporting Point (RvIG)',
    url: 'https://www.rvig.nl/centraal-meldpunt-identiteitsfraude',
  },
  {
    title: 'Woonbond: zo ontmasker je online oplichting',
    titleEn: 'Woonbond: how to unmask online rental fraud (Dutch)',
    url: 'https://www.woonbond.nl/thema/huren-recht/zo-ontmasker-je-online-oplichting/',
  },
  {
    title: 'NOS: nepadvertenties voor huurwoningen pieken in de zomer',
    titleEn: 'NOS: fake rental ads peak in summer (Dutch)',
    url: 'https://nos.nl/l/2624811',
  },
]

const nl: ScamGuideContent = {
  metaTitle: 'Oplichting woning herkennen: is deze huuradvertentie betrouwbaar? | De Huur Radar',
  metaDescription:
    'Pas op voor scam-woningen. Zo herken je oplichting bij een huurwoning of kamer: 10 signalen, een stappenplan om een woningadvertentie te checken en wat je doet als je al betaald hebt. Plus een gratis scamcheck.',
  breadcrumb: 'Oplichting herkennen',
  eyebrow: 'Gids · veilig huren',
  title: 'Oplichting met huurwoningen herkennen: zo check je of een advertentie betrouwbaar is',
  lead:
    'Nepadvertenties voor huurwoningen en kamers zien er steeds echter uit. Oplichters kopiëren foto’s van Funda of Pararius, vragen geld voor een bezichtiging en verdwijnen zodra je betaald hebt. Op deze pagina lees je hoe je een scam-woning herkent, hoe je een woningadvertentie stap voor stap controleert en wat je doet als het toch misgaat.',
  updatedLabel: 'Laatst bijgewerkt',
  shortAnswerTitle: 'Kort antwoord: zo check je een woningadvertentie',
  shortAnswer: [
    'Betaal nooit iets voor je de woning in het echt (of via live video met de verhuurder in de woning) hebt gezien en een huurcontract hebt.',
    'Zoek de foto’s op met Google Lens of een andere omgekeerde afbeeldingszoeker: staan ze ook in een andere advertentie, dan is het vaak nep.',
    'Controleer het adres en de eigenaar via het Kadaster, en een makelaar via de KvK.',
    'Vergelijk de huurprijs met vergelijkbare woningen in de buurt. Veel te goedkoop is een alarmsignaal.',
    'Wantrouw verhuurders die in het buitenland zitten, alleen via WhatsApp willen praten of haast maken.',
  ],
  ctaCheck: 'Check een advertentie gratis',
  ctaCheckNote: 'Plak de tekst of link en krijg een risicoscore van 0 tot 100 met uitleg. Geen account nodig.',
  signalsTitle: 'Pas op: 10 signalen van een scam-woning',
  signalsIntro:
    'Eén signaal betekent niet meteen dat het oplichting is. Zie je er meerdere tegelijk, stop dan en betaal niets.',
  signals: [
    {
      title: 'Je moet betalen voor de bezichtiging of het contract',
      body: 'Borg, eerste maand huur, “reserveringskosten” of een bedrag om de sleutel op te sturen: als je moet betalen voordat je de woning hebt gezien, is dat het sterkste signaal van woningoplichting.',
    },
    {
      title: 'De huurprijs is te mooi om waar te zijn',
      body: 'Een ruim appartement in Amsterdam of Utrecht voor de prijs van een kamer lokt veel reacties. Oplichters prijzen bewust laag om snel slachtoffers te vinden.',
    },
    {
      title: 'De verhuurder zit in het buitenland',
      body: 'Het verhaal is vaak dat de eigenaar voor werk of familie in het buitenland is en daarom niet kan laten zien. Kun je de woning niet bekijken, ga dan niet verder.',
    },
    {
      title: 'Contact alleen via WhatsApp, Telegram of e-mail',
      body: 'Wil de verhuurder niet bellen, geen videogesprek doen en het gesprek weg hebben van het platform? Dan is de kans groot dat hij iets te verbergen heeft.',
    },
    {
      title: 'Druk om vandaag nog te betalen',
      body: '“Er zijn nog tien andere geïnteresseerden” of “als je nu betaalt, is hij van jou”. Tijdsdruk is bedoeld om te voorkomen dat je dingen nagaat.',
    },
    {
      title: 'Betalen naar een buitenlandse rekening of via Western Union',
      body: 'Een buitenlands IBAN, Western Union, MoneyGram, crypto of een betaalverzoek via een onbekende link zijn bijna nooit nodig bij een echte Nederlandse huurwoning.',
    },
    {
      title: 'Foto’s die ook in andere advertenties staan',
      body: 'Gekopieerde foto’s van Funda, Pararius of een vakantieverhuursite zijn heel gangbaar. Let ook op details die niet kloppen, zoals buitenlandse stopcontacten.',
    },
    {
      title: 'Geen huisnummer of een adres dat niet bestaat',
      body: 'Een vaag adres (“centrum, dicht bij het station”) of een adres dat op Google Street View een kantoor of ander pand blijkt te zijn, is verdacht.',
    },
    {
      title: 'Je moet direct een kopie van je paspoort sturen',
      body: 'Met een kopie van je ID kunnen oplichters op jouw naam abonnementen afsluiten of leningen aanvragen. Stuur nooit een ID-kopie naar iemand die je niet hebt ontmoet, en gebruik altijd de KopieID-app.',
    },
    {
      title: 'Een Engelstalig standaardverhaal',
      body: 'Teksten in stijf Engels of met zinnen als “God bless you” en “the keys will be sent by courier” komen vaak uit vaste scam-sjablonen.',
    },
  ],
  stepsTitle: 'Hoe check je of een woningadvertentie betrouwbaar is? Stappenplan',
  stepsIntro:
    'Neem voor elke advertentie die je serieus overweegt deze stappen door. Het kost tien minuten en kan je honderden of duizenden euro’s besparen.',
  steps: [
    {
      title: 'Laat de advertentie automatisch checken',
      body: 'Plak de tekst of link in De Huur Radar. Je ziet meteen welke signalen erin staan (betaalroute, druk, prijs ten opzichte van de stad) en een risicoscore met uitleg.',
    },
    {
      title: 'Zoek de foto’s omgekeerd op',
      body: 'Sla een foto op en zoek hem op met Google Lens, Bing Visual Search of TinEye. Vind je dezelfde foto’s bij een ander adres, een andere prijs of een vakantiewoning, dan is de advertentie vrijwel zeker nep.',
    },
    {
      title: 'Controleer adres en eigenaar',
      body: 'Kijk op Google Maps of Street View of het pand bestaat en lijkt op de foto’s. Via het Kadaster zie je voor een paar euro wie de eigenaar is. Komt die naam niet overeen met de verhuurder, vraag dan waarom.',
    },
    {
      title: 'Zoek de verhuurder of makelaar op',
      body: 'Een makelaar of verhuurbedrijf staat in het Handelsregister van de KvK en heeft een eigen website met vast telefoonnummer. Zoek ook op de naam plus “oplichting” of “scam”.',
    },
    {
      title: 'Vergelijk de huurprijs',
      body: 'Bekijk wat vergelijkbare woningen in dezelfde stad of wijk kosten op betrouwbare huurwebsites. Ligt de prijs ver onder het gemiddelde, wees dan extra kritisch.',
    },
    {
      title: 'Bezichtig de woning in het echt',
      body: 'Ga zelf kijken of stuur iemand die je vertrouwt. Kan dat niet, vraag dan een live videogesprek waarin de verhuurder door de woning loopt en iets laat zien wat jij vraagt.',
    },
    {
      title: 'Betaal pas na een getekend contract, naar een Nederlandse rekening',
      body: 'Lees het huurcontract goed, controleer of de naam op het contract klopt met de rekeninghouder en betaal via een gewone overboeking. Nooit contant, via Western Union of met crypto.',
    },
  ],
  typesTitle: 'Veelvoorkomende vormen van oplichting bij woningen',
  typesIntro: 'De trucs veranderen, maar de meeste woningscams vallen in een van deze vormen.',
  types: [
    {
      title: 'De gekopieerde advertentie',
      body: 'Een bestaande woning wordt met dezelfde foto’s opnieuw aangeboden, maar dan goedkoper en door een “verhuurder” die niet de eigenaar is. Komt veel voor op Facebook, Marktplaats, TikTok en Kamernet.',
    },
    {
      title: 'De verhuurder in het buitenland',
      body: 'De eigenaar kan niet laten zien, maar stuurt de sleutel op na betaling van borg en eerste maand. Soms via een nep-Airbnb-pagina of een “koeriersdienst”.',
    },
    {
      title: 'De nepbezichtiging',
      body: 'Oplichters huren een woning kort via een vakantieverhuursite en laten hem aan meerdere mensen zien. Iedereen tekent een contract en betaalt borg, daarna is de verhuurder onbereikbaar.',
    },
    {
      title: 'Betalen voor een bezichtiging of reservering',
      body: 'Vooral bij studentenkamers via social media: je betaalt € 100 tot € 200 om te mogen kijken of de kamer “vast te houden”. Daarna hoor je niets meer.',
    },
    {
      title: 'Phishing via een huurwebsite',
      body: 'Je krijgt een bericht dat lijkt te komen van Kamernet, Pararius, WoningNet of Booking.com, met een link om je account te verlengen of je betaling te bevestigen. De link leidt naar een nepsite.',
    },
    {
      title: 'Identiteitsfraude',
      body: 'Er is helemaal geen woning; het doel is je paspoort, BSN of loonstrook. Die worden gebruikt om op jouw naam leningen of abonnementen af te sluiten.',
    },
  ],
  paidTitle: 'Al betaald of je ID gestuurd? Dit doe je nu',
  paidIntro: 'Snel handelen vergroot de kans dat je geld terugkomt en beperkt verdere schade.',
  paidSteps: [
    'Bel direct je bank en vraag of de betaling teruggedraaid of geblokkeerd kan worden.',
    'Maak screenshots van de advertentie, alle berichten, het profiel van de verhuurder en je betaalbewijs.',
    'Doe aangifte bij de politie, online of op het bureau.',
    'Meld de oplichting bij de Fraudehelpdesk, zodat anderen gewaarschuwd kunnen worden.',
    'Meld de advertentie en het profiel bij het platform waar je hem vond.',
    'Heb je een kopie van je ID gestuurd? Meld dat bij het Centraal Meldpunt Identiteitsfraude en houd je bankrekening en post extra in de gaten.',
  ],
  faqTitle: 'Veelgestelde vragen over oplichting bij huurwoningen',
  faq: [
    {
      q: 'Hoe weet ik of een huurwoning nep is?',
      a: 'Een nep-huurwoning heeft meestal een combinatie van deze kenmerken: een lage prijs, een verhuurder die niet kan laten zien, contact alleen via WhatsApp of e-mail, en de vraag om vooraf te betalen. Zoek de foto’s omgekeerd op en controleer het adres via het Kadaster. Twijfel je, plak de advertentie dan in De Huur Radar voor een risicoscore met uitleg.',
    },
    {
      q: 'Is het normaal om borg te betalen voor de bezichtiging?',
      a: 'Nee. Bij een betrouwbare verhuurder bezichtig je eerst, teken je daarna een huurcontract en betaal je pas dan borg en eerste huur. Wie geld vraagt voor een bezichtiging of om een woning “vast te houden”, is bijna altijd een oplichter.',
    },
    {
      q: 'Hoe check ik of een verhuurder echt de eigenaar is?',
      a: 'Vraag het volledige adres en zoek de eigenaar op via eigendomsinformatie van het Kadaster. Komt de naam niet overeen, vraag dan om bewijs dat de verhuurder mag (onder)verhuren, zoals een machtiging of het huurcontract van de hoofdhuurder. Bij een makelaar controleer je de inschrijving bij de KvK.',
    },
    {
      q: 'Ik heb al betaald aan een nepverhuurder. Krijg ik mijn geld terug?',
      a: 'Dat is niet zeker, maar bel zo snel mogelijk je bank: soms kan een overboeking nog worden tegengehouden. Doe daarnaast aangifte bij de politie en meld het bij de Fraudehelpdesk. Bewaar alle berichten en betaalbewijzen.',
    },
    {
      q: 'Zijn advertenties op Facebook, Marktplaats of TikTok vaker nep?',
      a: 'Op social media en open advertentieplatforms kan iedereen zonder controle een woning plaatsen, dus daar komt woningoplichting vaker voor. Ook op bekende huurwebsites duiken soms nepadvertenties op. Kies bij voorkeur sites met verificatie en wees overal alert op dezelfde signalen.',
    },
    {
      q: 'Mag ik een kopie van mijn paspoort sturen naar een verhuurder?',
      a: 'Pas als je weet dat de verhuurder echt is, bijvoorbeeld na een bezichtiging. Gebruik altijd de KopieID-app van de Rijksoverheid: daarmee streep je je BSN en pasfoto door en zet je er een watermerk op met het doel en de datum.',
    },
    {
      q: 'Kan De Huur Radar zeker zeggen of een advertentie oplichting is?',
      a: 'Nee. De Huur Radar zoekt naar bekende signalen van huurfraude en geeft een risicoscore met uitleg. Een lage score is geen garantie dat alles klopt, dus doe altijd ook de controles uit het stappenplan hierboven.',
    },
  ],
  sourcesTitle: 'Bronnen en meldpunten',
  sourcesIntro: 'Deze gids is gebaseerd op adviezen en meldingen van onder andere:',
  ctaBottomTitle: 'Twijfel je over een advertentie?',
  ctaBottomBody: 'Plak de tekst of link in De Huur Radar. Je ziet binnen een paar seconden welke signalen erin staan en hoe hoog het risico is.',
  ctaFaq: 'Meer vragen en antwoorden',
  ctaSites: 'Betrouwbare huurwebsites',
}

const en: ScamGuideContent = {
  metaTitle: 'How to spot a rental scam in the Netherlands: is this listing legit? | De Huur Radar',
  metaDescription:
    'Spot fake rental listings before you pay. 10 warning signs of a housing scam, a step-by-step check for any rental ad in the Netherlands and what to do if you already paid. Plus a free scam check.',
  breadcrumb: 'Spot a rental scam',
  eyebrow: 'Guide · safe renting',
  title: 'How to spot a rental scam: check whether a housing ad is trustworthy',
  lead:
    'Fake listings for apartments and rooms in the Netherlands look more convincing every year. Scammers copy photos from Funda or Pararius, charge a fee for a viewing and disappear once you have paid. This page explains how to recognise a fake rental, how to check a listing step by step and what to do if things go wrong.',
  updatedLabel: 'Last updated',
  shortAnswerTitle: 'Short answer: how to check a rental listing',
  shortAnswer: [
    'Never pay anything before you have seen the home in person (or on a live video call with the landlord inside it) and signed a lease.',
    'Run the photos through Google Lens or another reverse image search. If they show up in another listing, it is often fake.',
    'Check the address and owner via the Kadaster, and an agency via the KvK trade register.',
    'Compare the rent with similar homes nearby. Far too cheap is a red flag.',
    'Be wary of landlords who are abroad, only want to talk on WhatsApp or rush you.',
  ],
  ctaCheck: 'Check a listing for free',
  ctaCheckNote: 'Paste the text or link and get a 0–100 risk score with an explanation. No account needed.',
  signalsTitle: 'Watch out: 10 signs of a rental scam',
  signalsIntro: 'One sign does not prove fraud. If you see several at once, stop and do not pay.',
  signals: [
    { title: 'You must pay for the viewing or the contract', body: 'Deposit, first month, “reservation fee” or money to post the keys: paying before you have seen the home is the strongest sign of rental fraud.' },
    { title: 'The rent is too good to be true', body: 'A spacious flat in Amsterdam or Utrecht for the price of a room attracts lots of replies. Scammers price low on purpose.' },
    { title: 'The landlord is abroad', body: 'The owner is supposedly abroad for work or family and cannot show the home. If you cannot view it, walk away.' },
    { title: 'Contact only via WhatsApp, Telegram or email', body: 'A landlord who will not call, refuses a video call and moves the chat off the platform usually has something to hide.' },
    { title: 'Pressure to pay today', body: '“Ten other people are interested” or “pay now and it is yours”. Time pressure is meant to stop you from checking.' },
    { title: 'Payment to a foreign account or via Western Union', body: 'A foreign IBAN, Western Union, MoneyGram, crypto or a payment link from an unknown site are almost never needed for a real Dutch rental.' },
    { title: 'Photos that appear in other listings', body: 'Copied photos from Funda, Pararius or holiday rentals are very common. Watch for details that do not fit, like foreign power sockets.' },
    { title: 'No house number or an address that does not exist', body: 'A vague address (“city centre, close to the station”) or one that turns out to be an office on Street View is suspicious.' },
    { title: 'You must send a passport copy straight away', body: 'With a copy of your ID, scammers can take out contracts or loans in your name. Never send it to someone you have not met, and always use the KopieID app.' },
    { title: 'A generic English story', body: 'Stiff English or phrases like “God bless you” and “the keys will be sent by courier” often come from standard scam templates.' },
  ],
  stepsTitle: 'How do you check whether a rental listing is trustworthy? Step by step',
  stepsIntro: 'Go through these steps for every listing you seriously consider. It takes ten minutes and can save you hundreds or thousands of euros.',
  steps: [
    { title: 'Run an automatic check', body: 'Paste the text or link into De Huur Radar. You immediately see which warning signs it contains (payment route, pressure, price versus the city) and a risk score with an explanation.' },
    { title: 'Reverse-search the photos', body: 'Save a photo and search it with Google Lens, Bing Visual Search or TinEye. Same photos at another address, price or holiday home means the ad is almost certainly fake.' },
    { title: 'Check the address and owner', body: 'Use Google Maps or Street View to see whether the building exists and matches the photos. The Kadaster shows the owner for a few euros. If the name differs from the landlord, ask why.' },
    { title: 'Look up the landlord or agency', body: 'An agency is listed in the KvK trade register and has its own website and landline. Also search the name plus “scam”.' },
    { title: 'Compare the rent', body: 'See what similar homes in the same city or area cost on trusted rental sites. If the price is far below average, be extra critical.' },
    { title: 'View the home in person', body: 'Go yourself or send someone you trust. If that is impossible, ask for a live video call where the landlord walks through the home and shows something you ask for.' },
    { title: 'Pay only after a signed lease, to a Dutch account', body: 'Read the lease, check that the name matches the account holder and pay by regular bank transfer. Never in cash, via Western Union or with crypto.' },
  ],
  typesTitle: 'Common types of rental scams',
  typesIntro: 'The tricks change, but most housing scams fall into one of these types.',
  types: [
    { title: 'The cloned listing', body: 'An existing home is offered again with the same photos, but cheaper and by a “landlord” who is not the owner. Common on Facebook, Marktplaats, TikTok and Kamernet.' },
    { title: 'The landlord abroad', body: 'The owner cannot show the home but will post the keys after you pay deposit and first month, sometimes via a fake Airbnb page or “courier”.' },
    { title: 'The fake viewing', body: 'Scammers rent a place briefly via a holiday rental site and show it to many people. Everyone signs and pays a deposit, then the landlord vanishes.' },
    { title: 'Paying for a viewing or reservation', body: 'Especially for student rooms on social media: you pay €100 to €200 to view or “hold” the room, then never hear back.' },
    { title: 'Phishing via a rental site', body: 'A message that looks like Kamernet, Pararius, WoningNet or Booking.com asks you to renew your account or confirm a payment. The link leads to a fake site.' },
    { title: 'Identity fraud', body: 'There is no home at all; the goal is your passport, BSN or payslip, used to take out loans or contracts in your name.' },
  ],
  paidTitle: 'Already paid or sent your ID? Do this now',
  paidIntro: 'Acting fast improves the chance of getting money back and limits further damage.',
  paidSteps: [
    'Call your bank right away and ask whether the payment can be reversed or blocked.',
    'Take screenshots of the listing, all messages, the landlord’s profile and your proof of payment.',
    'Report it to the police, online or at a station.',
    'Report the scam to the Fraudehelpdesk so others can be warned.',
    'Report the listing and profile to the platform where you found it.',
    'Sent a copy of your ID? Report it to the Centraal Meldpunt Identiteitsfraude and keep a close eye on your bank account and post.',
  ],
  faqTitle: 'Frequently asked questions about rental scams',
  faq: [
    { q: 'How do I know if a rental listing is fake?', a: 'A fake rental usually combines several of these: a low price, a landlord who cannot show the home, contact only via WhatsApp or email, and a request to pay upfront. Reverse-search the photos and check the address with the Kadaster. In doubt, paste the listing into De Huur Radar for a risk score with an explanation.' },
    { q: 'Is it normal to pay a deposit before a viewing?', a: 'No. With a genuine landlord you view first, then sign a lease, and only then pay the deposit and first month. Anyone asking money for a viewing or to “hold” a home is almost always a scammer.' },
    { q: 'How do I check whether a landlord really owns the home?', a: 'Ask for the full address and look up the owner via the Kadaster. If the name differs, ask for proof the landlord may (sub)let, such as an authorisation or the main tenant’s lease. For an agency, check its KvK registration.' },
    { q: 'I already paid a fake landlord. Will I get my money back?', a: 'Not guaranteed, but call your bank as soon as possible: a transfer can sometimes still be stopped. Also report it to the police and the Fraudehelpdesk, and keep all messages and receipts.' },
    { q: 'Are listings on Facebook, Marktplaats or TikTok more often fake?', a: 'On social media and open classifieds anyone can post a home without checks, so rental fraud is more common there. Fake listings sometimes appear on well-known rental sites too. Prefer sites with verification and watch for the same signs everywhere.' },
    { q: 'Can I send a passport copy to a landlord?', a: 'Only once you know the landlord is real, for example after a viewing. Always use the government’s KopieID app to cross out your BSN and photo and add a watermark with purpose and date.' },
    { q: 'Can De Huur Radar say for sure whether a listing is a scam?', a: 'No. De Huur Radar looks for known signs of rental fraud and gives a risk score with an explanation. A low score is no guarantee, so always do the checks from the step-by-step plan above as well.' },
  ],
  sourcesTitle: 'Sources and where to report',
  sourcesIntro: 'This guide is based on advice and reports from, among others:',
  ctaBottomTitle: 'Not sure about a listing?',
  ctaBottomBody: 'Paste the text or link into De Huur Radar. Within seconds you see which warning signs it contains and how high the risk is.',
  ctaFaq: 'More questions and answers',
  ctaSites: 'Trusted rental sites',
}

export const scamGuide: Record<'nl' | 'en', ScamGuideContent> = { nl, en }
