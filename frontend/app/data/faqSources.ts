/**
 * Externe bronnen over huurfraude / veilig huren (NL focus, EU-aanvulling).
 * Geen endorsement — ter informatie en verdere lectuur.
 *
 * Server-side classificatie (kamer / hele woning, particulier / sociale huur) gebruikt
 * dezelfde soort NL-termen als in deze bronnen; zie `app/Services/ListingDwellingClassifier.php`.
 */
export type FaqSource = { title: string; titleEn: string; url: string }

export const faqSources: FaqSource[] = [
  {
    title: '!WOON / Wooninfo — tips tegen oplichters bij huren',
    titleEn: '!WOON / Wooninfo — tips on rental scams',
    url: 'https://www.wooninfo.nl/vraagbaak/woning-zoeken/oplichters/',
  },
  {
    title: 'Huurstunt — Facebook-scam & huurwoning herkennen',
    titleEn: 'Huurstunt — recognizing Facebook rental scams',
    url: 'https://www.huurstunt.nl/blog/facebook-scam-huurwoning-herkennen',
  },
  {
    title: 'BNNVARA Kassa — communityvraag huur-scams',
    titleEn: 'BNNVARA Kassa — discussion on rental scams',
    url: 'https://www.bnnvara.nl/kassa/vraag-beantwoord/winkels-webshops/huur-scams',
  },
  {
    title: 'Stekkies — veilig huren, scams herkennen (2025)',
    titleEn: 'Stekkies — safe renting & scam signals',
    url: 'https://www.stekkies.com/nl/blog/veilig-huren-2025-stekkies/',
  },
  {
    title: 'Huurwoningen Nederland — spam & nep-accounts',
    titleEn: 'Huurwoningen Nederland — spam & fake accounts',
    url: 'https://www.huurwoningennederland.nl/spam-nep-accounts',
  },
  {
    title: 'Scholieren.com — kamer zoeken: 4 tips tegen oplichting (o.a. CCV / ministerie J&V)',
    titleEn: 'Scholieren.com — room search: 4 tips to avoid scams (Dutch students)',
    url: 'https://www.scholieren.com/blog/kamer-zoeken-4-tips-om-te-voorkomen-dat-je-wordt-opgelicht',
  },
  {
    title: 'Pararius — voorkom oplichting',
    titleEn: 'Pararius — prevent fraud',
    url: 'https://www.pararius.nl/info/voorkom-oplichting',
  },
  {
    title: 'Pararius — wat Pararius doet om fraude te voorkomen',
    titleEn: 'Pararius — anti-fraud measures',
    url: 'https://www.pararius.nl/nieuws/wat-pararius-doet-om-fraude-te-voorkomen',
  },
  {
    title: 'Fraudehelpdesk.nl',
    titleEn: 'Fraudehelpdesk.nl (Dutch fraud helpdesk)',
    url: 'https://www.fraudehelpdesk.nl/',
  },
  {
    title: 'Politie — aangifte doen',
    titleEn: 'Dutch police — filing a report',
    url: 'https://www.politie.nl/onderwerpen/aangifte-doen.html',
  },
  {
    title: 'Kadaster — eigendomsinformatie woning',
    titleEn: 'Kadaster — property ownership information',
    url: 'https://www.kadaster.nl/producten/woning/eigendomsinformatie',
  },
  {
    title: 'Rijksoverheid — identiteitsfraude',
    titleEn: 'Dutch government — identity fraud',
    url: 'https://www.rijksoverheid.nl/onderwerpen/identiteitsfraude',
  },
  {
    title: 'Veilig internetten — tips & signalen',
    titleEn: 'Veilig internetten (safe internet)',
    url: 'https://www.veiliginternetten.nl/',
  },
  {
    title: 'Consumentenbond — wonen',
    titleEn: 'Consumentenbond — housing (NL)',
    url: 'https://www.consumentenbond.nl/wonen/',
  },
  {
    title: 'NVM — verhuurders & consument',
    titleEn: 'NVM — landlords & consumers',
    url: 'https://www.nvm.nl/consument/',
  },
  {
    title: 'ACM — consumentenbescherming',
    titleEn: 'ACM — consumer protection (NL)',
    url: 'https://www.acm.nl/nl/onderwerpen/consumentenbescherming',
  },
  {
    title: 'Marktplaats — veiligheidscentrum',
    titleEn: 'Marktplaats — safety centre',
    url: 'https://www.marktplaats.nl/m/veiligheidscentrum/',
  },
  {
    title: 'Juridisch Loket — huren (overzicht)',
    titleEn: 'Juridisch Loket — renting (NL public legal desk)',
    url: 'https://www.juridischloket.nl/alle-onderwerpen/huren/',
  },
  {
    title: 'Rijksoverheid — huren in Nederland (overzicht)',
    titleEn: 'Dutch government — renting in the Netherlands',
    url: 'https://www.rijksoverheid.nl/onderwerpen/huurwoning',
  },
  {
    title: 'EC Europa — consumentenrechten',
    titleEn: 'EU — consumer rights',
    url: 'https://european-union.europa.eu/topics/consumer-rights_nl',
  },
  {
    title: 'Nibud — budget & wonen',
    titleEn: 'Nibud — budgeting & housing (NL)',
    url: 'https://www.nibud.nl/onderwerpen/wonen/',
  },
  {
    title: 'Radar (AVROTROS) — consumentenprogramma',
    titleEn: 'Radar (AVROTROS) — consumer programme',
    url: 'https://www.avrotros.nl/radar/',
  },
  {
    title: 'Wikipedia — rental fraud (context, internationaal)',
    titleEn: 'Wikipedia — rental scam (international context)',
    url: 'https://en.wikipedia.org/wiki/Rental_scam',
  },
  {
    title: 'Huurcommissie — geschillen over huurprijs, servicekosten en meer',
    titleEn: 'Rent Tribunal (Huurcommissie) — rental disputes (NL)',
    url: 'https://www.huurcommissie.nl/',
  },
  {
    title: 'Woonbond — zo ontmasker je online oplichting bij het zoeken naar een woning',
    titleEn: 'Woonbond — how to spot online rental scams',
    url: 'https://www.woonbond.nl/thema/huren-recht/zo-ontmasker-je-online-oplichting/',
  },
  {
    title: 'Woonbond — honderden meldingen van verhuurfraude (nieuws)',
    titleEn: 'Woonbond — hundreds of reported rental fraud cases',
    url: 'https://www.woonbond.nl/nieuws/honderden-gemelde-zaken-van-verhuurfraude/',
  },
  {
    title: 'Woonbond — laat je niet oplichten bij het zoeken naar woonruimte',
    titleEn: 'Woonbond — don’t get scammed when searching for housing',
    url: 'https://www.woonbond.nl/thema/huren-recht/laat-je-niet-oplichten-zoeken-woonruimte/',
  },
  {
    title: 'ACM ConsuWijzer — let op misleiding op huurwoningwebsites',
    titleEn: 'ACM ConsuWijzer — misleading rental listing sites',
    url: 'https://www.consuwijzer.nl/nieuws/let-op-voor-misleiding-acm-onderzoekt-huurwoningwebsites',
  },
  {
    title: 'ACM ConsuWijzer — fraude, oplichting en oneerlijke handelspraktijken',
    titleEn: 'ACM ConsuWijzer — fraud and unfair commercial practices',
    url: 'https://www.consuwijzer.nl/thema/fraude-oplichting-en-oneerlijke-handelspraktijken',
  },
  {
    title: 'Autoriteit Persoonsgegevens — veilige kopie van je ID (o.a. KopieID)',
    titleEn: 'Dutch DPA — safe copies of your ID document',
    url: 'https://www.autoriteitpersoonsgegevens.nl/themas/identificatie/paspoort-en-identiteitskaart/kopie-van-uw-id-bewijs-wat-kunt-u-doen',
  },
  {
    title: 'Europees Consumenten Centrum Nederland — hulp bij grensoverschrijdende consumentenzaken (EU)',
    titleEn: 'European Consumer Centre Netherlands — cross-border consumer issues',
    url: 'https://www.eccnederland.nl/',
  },
  {
    title: 'RvIG — Centraal Meldpunt Identiteitsfraude (CMI)',
    titleEn: 'RvIG — Central Identity Fraud Hotline (CMI)',
    url: 'https://www.rvig.nl/centraal-meldpunt-identiteitsfraude',
  },
  {
    title: 'NCSC — hoe herken je een phishingmail?',
    titleEn: 'NCSC (NL) — how to recognise phishing email',
    url: 'https://www.ncsc.nl/phishing/hoe-herken-ik-een-phishing-e-mail',
  },
  {
    title: 'Kamernet — hoe herken ik een oplichter? (support)',
    titleEn: 'Kamernet — how to recognise a scammer (support)',
    url: 'https://support.kamernet.nl/hoe-herken-ik-een-oplichter',
  },
  {
    title: 'Rijksoverheid — welke rechten en plichten heb ik als huurder?',
    titleEn: 'Dutch government — tenant rights and obligations',
    url: 'https://www.rijksoverheid.nl/onderwerpen/woning-huren/vraag-en-antwoord/welke-rechten-en-plichten-heb-ik-als-huurder',
  },
  {
    title: 'Meld Misdaad Anoniem',
    titleEn: 'Report Crime Anonymously (NL)',
    url: 'https://www.meldmisdaadanoniem.nl/',
  },
  {
    title: 'ACM — onderzoek naar huurwoningwebsites die consumenten misleiden',
    titleEn: 'ACM — investigation into misleading rental listing websites',
    url: 'https://www.acm.nl/nl/publicaties/acm-doet-onderzoek-naar-huurwoningwebsites-die-consumenten-misleiden',
  },
  {
    title: 'Politie — internetoplichting (uitleg en wat je kunt doen)',
    titleEn: 'Dutch police — internet fraud',
    url: 'https://www.politie.nl/onderwerpen/internetoplichting.html',
  },
  {
    title: 'Rijksoverheid — veilige kopie van je identiteitsbewijs (KopieID-app)',
    titleEn: 'Dutch government — secure ID copy (KopieID app)',
    url: 'https://www.rijksoverheid.nl/onderwerpen/identiteitsfraude/vraag-en-antwoord/veilige-kopie-identiteitsbewijs',
  },
  {
    title: 'Europese Commissie — housing checklist: veilig studentenhuis in Europa',
    titleEn: 'European Commission — housing checklist for students in Europe',
    url: 'https://education.ec.europa.eu/node/3200',
  },
  {
    title: 'LSVb — hulp bij het vinden van een studentenkamer',
    titleEn: 'National Union of Students (NL) — finding a student room',
    url: 'https://lsvb.nl/alles-over-je-studententijd/huisvesting/hulp-bij-het-vinden-van-een-studentenkamer/',
  },
  {
    title: 'LSVb — Housing Hotline (advies bij huisvesting / fraude)',
    titleEn: 'LSVb — Housing Hotline',
    url: 'https://lsvb.nl/diensten/housing-hotline/',
  },
  {
    title: 'Veilig Bankieren (NVB) — phishing en betaalfraude',
    titleEn: 'Veilig Bankieren — phishing and payment fraud (NL banks)',
    url: 'https://www.veiligbankieren.nl/fraude/phishing',
  },
]
