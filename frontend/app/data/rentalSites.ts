/**
 * Lijst van bekende NL huurwebsites met indicatie of de huurder/zoeker er gratis terecht kan.
 * Geen endorsement — alleen ter oriëntatie. “Pricing” verandert; controleer altijd zelf.
 *
 * Kostenindicatie:
 *  - free       : zoeken én reageren op een advertentie kost de zoeker niets
 *  - freemium   : basisgebruik gratis, contact/reageren vereist betaald abonnement of premium
 *  - paid       : inschrijfgeld of vast tarief om te kunnen zoeken/reageren
 *
 * Regio-indeling: meeste platforms zijn “landelijk”. Enkele woningcorporaties of
 * studentenhuisvesters zijn aan een specifieke regio of meerdere steden gekoppeld.
 */
export type RentalSiteCost = 'free' | 'freemium' | 'paid'

export type RentalSiteRegion =
  | 'landelijk'
  | 'multi'
  | 'noord-holland'
  | 'zuid-holland'
  | 'utrecht'
  | 'noord-brabant'
  | 'limburg'
  | 'internationaal'

export interface RentalSite {
  /** Korte herkenbare naam, identiek in NL en EN. */
  name: string
  /** Volledige (https) URL — homepage of overzicht. */
  url: string
  /** Indicatie kosten voor de huurder/zoeker. */
  cost: RentalSiteCost
  /** Hoofdregio waar het platform zich op richt. */
  region: RentalSiteRegion
  /** Optioneel: lijst van steden/gebieden voor extra context bij multi- of regionale sites. */
  cities?: string[]
  /** Korte uitleg in NL (1 zin) — wat voor advertenties hier staan. */
  noteNl: string
  /** Korte uitleg in EN (1 zin). */
  noteEn: string
}

export const rentalSites: RentalSite[] = [
  // ── Landelijk: vrije sector ──
  {
    name: 'Funda',
    url: 'https://www.funda.nl/huur/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Grootste NL platform voor koop én huur; zoeken en reageren is voor de huurder gratis.',
    noteEn: 'Largest Dutch rental & sales portal; searching and contacting is free for tenants.',
  },
  {
    name: 'Pararius',
    url: 'https://www.pararius.nl/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Vrije-sector huurwoningen via aangesloten makelaars; voor de huurder altijd gratis.',
    noteEn: 'Free-sector rentals via affiliated agents; always free for the tenant.',
  },
  {
    name: 'Huurwoningen.nl',
    url: 'https://www.huurwoningen.nl/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Aanbod van makelaars en verhuurders; reageren via de makelaar is gratis.',
    noteEn: 'Listings from agents and landlords; replying via the agent is free.',
  },
  {
    name: 'Direct Wonen',
    url: 'https://www.directwonen.nl/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Particuliere en professionele verhuur; gratis voor de zoeker.',
    noteEn: 'Private and professional rentals; free for searchers.',
  },
  {
    name: '123Wonen',
    url: 'https://123wonen.nl/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Verhuurplatform met eigen vestigingen; reageren is gratis.',
    noteEn: 'Rental platform with regional offices; replying is free.',
  },
  {
    name: 'Rotsvast',
    url: 'https://www.rotsvast.nl/woningaanbod/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Landelijke verhuurketen; geen bemiddelingskosten voor de huurder.',
    noteEn: 'Nationwide rental agency chain; no broker fees for tenants.',
  },
  {
    name: 'Vesteda',
    url: 'https://www.vesteda.com/nl/woning-huren',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Grote institutionele verhuurder (vrije sector); reageren is gratis.',
    noteEn: 'Large institutional landlord (free sector); replying is free.',
  },
  {
    name: 'Bouwinvest',
    url: 'https://www.bouwinvest.com/woning-huren/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Grote institutionele verhuurder; reageren via de website is gratis.',
    noteEn: 'Major institutional landlord; replying via the website is free.',
  },
  {
    name: 'Woonnet (Wonen Nederland)',
    url: 'https://www.woonnet.nl/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Aanbod van NVM/aangesloten makelaars; gratis voor de zoeker.',
    noteEn: 'Listings from NVM-affiliated agents; free for searchers.',
  },
  {
    name: 'Funda in Business (huur)',
    url: 'https://www.fundainbusiness.nl/huur/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Bedrijfsruimte/woon-werk; gratis voor de zoeker.',
    noteEn: 'Commercial/live-work spaces; free for searchers.',
  },
  {
    name: 'Marktplaats — Huizen & Kamers',
    url: 'https://www.marktplaats.nl/l/huizen-en-kamers/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Particulier aanbod; gratis te bekijken en reageren — extra alert zijn op scams.',
    noteEn: 'Private classifieds; free to browse and reply — be extra alert for scams.',
  },
  {
    name: 'Hospi Housing',
    url: 'https://www.hospihousing.com/nl/',
    cost: 'free',
    region: 'landelijk',
    noteNl: 'Hospita-verhuur via NL-universiteiten; voor studenten gratis.',
    noteEn: 'Host-family lodging via Dutch universities; free for students.',
  },
  {
    name: 'Kamernet',
    url: 'https://kamernet.nl/',
    cost: 'freemium',
    region: 'landelijk',
    noteNl: 'Veel kameraanbod; reageren op advertenties vereist een betaald abonnement.',
    noteEn: 'Wide range of rooms; replying to ads requires a paid subscription.',
  },
  {
    name: 'Kamer.nl',
    url: 'https://www.kamer.nl/',
    cost: 'freemium',
    region: 'landelijk',
    noteNl: 'Kamers en studio’s; reageren vereist een betaald lidmaatschap.',
    noteEn: 'Rooms and studios; messaging requires a paid membership.',
  },

  // ── Multi-regio (specifieke steden in meerdere provincies) ──
  {
    name: 'ROOM.nl',
    url: 'https://www.room.nl/',
    cost: 'free',
    region: 'multi',
    cities: ['Delft', 'Amsterdam', 'Leiden', 'Den Haag', 'Wageningen'],
    noteNl: 'Studentenkamers van studentenhuisvesters (DUWO e.a.); gratis inschrijving en reageren.',
    noteEn: 'Student rooms from major Dutch student housing providers; free to register and apply.',
  },
  {
    name: 'DUWO',
    url: 'https://www.duwo.nl/',
    cost: 'free',
    region: 'multi',
    cities: ['Delft', 'Amsterdam', 'Leiden', 'Den Haag'],
    noteNl: 'Studentenhuisvester in o.a. Delft, Amsterdam, Leiden en Den Haag; reageren via ROOM.nl is gratis.',
    noteEn: 'Student housing provider in Delft, Amsterdam, Leiden and The Hague; applying via ROOM.nl is free.',
  },
  {
    name: 'SSH (Stichting Studenten Huisvesting)',
    url: 'https://www.sshxl.nl/',
    cost: 'paid',
    region: 'multi',
    cities: ['Utrecht', 'Groningen', 'Maastricht', 'Rotterdam', 'Tilburg'],
    noteNl: 'Studentenkamers in o.a. Utrecht, Groningen, Maastricht en Rotterdam; jaarlijkse inschrijfvergoeding.',
    noteEn: 'Student rooms in Utrecht, Groningen, Maastricht and Rotterdam; small annual registration fee.',
  },

  // ── Noord-Holland ──
  {
    name: 'WoningNet regio Amsterdam',
    url: 'https://www.woningnetregioamsterdam.nl/',
    cost: 'paid',
    region: 'noord-holland',
    cities: ['Amsterdam', 'Amstelveen', 'Diemen', 'Haarlemmermeer', 'Zaanstad'],
    noteNl: 'Sociale huurwoningen in regio Amsterdam via woningcorporaties; klein jaarlijks inschrijfgeld.',
    noteEn: 'Social housing in the Amsterdam region via housing corporations; small yearly registration fee.',
  },

  // ── Zuid-Holland ──
  {
    name: 'Woonnet Haaglanden',
    url: 'https://www.woonnet-haaglanden.nl/',
    cost: 'paid',
    region: 'zuid-holland',
    cities: ['Den Haag', 'Delft', 'Rijswijk', 'Zoetermeer', 'Westland'],
    noteNl: 'Sociale huur regio Haaglanden; klein jaarlijks inschrijfgeld voor reageren.',
    noteEn: 'Social housing in the Haaglanden region; small yearly registration fee.',
  },
  {
    name: 'Woonnet Rijnmond',
    url: 'https://www.woonnetrijnmond.nl/',
    cost: 'paid',
    region: 'zuid-holland',
    cities: ['Rotterdam', 'Schiedam', 'Vlaardingen', 'Capelle aan den IJssel', 'Spijkenisse'],
    noteNl: 'Sociale huur regio Rotterdam-Rijnmond; klein inschrijfbedrag.',
    noteEn: 'Social housing in the Rotterdam-Rijnmond region; small registration fee.',
  },

  // ── Utrecht ──
  {
    name: 'WoningNet regio Utrecht',
    url: 'https://www.woningnetregioutrecht.nl/',
    cost: 'paid',
    region: 'utrecht',
    cities: ['Utrecht', 'Nieuwegein', 'IJsselstein', 'Zeist', 'Houten'],
    noteNl: 'Sociale huur regio Utrecht; klein jaarlijks inschrijfgeld.',
    noteEn: 'Social housing in the Utrecht region; small yearly registration fee.',
  },

  // ── Noord-Brabant ──
  {
    name: 'Wooniezie',
    url: 'https://www.wooniezie.nl/',
    cost: 'paid',
    region: 'noord-brabant',
    cities: ['Eindhoven', 'Helmond', 'Veldhoven', 'Geldrop', 'Best'],
    noteNl: 'Sociale huur regio Eindhoven (Stedelijk Gebied Eindhoven); klein inschrijfbedrag.',
    noteEn: 'Social housing in the Eindhoven region; small registration fee.',
  },

  // ── Limburg ──
  {
    name: 'Thuis in Limburg',
    url: 'https://www.thuisinlimburg.nl/',
    cost: 'paid',
    region: 'limburg',
    cities: ['Maastricht', 'Heerlen', 'Roermond', 'Sittard-Geleen', 'Venlo'],
    noteNl: 'Sociale huur via samenwerkende corporaties in heel Limburg; klein inschrijfbedrag.',
    noteEn: 'Social housing via joined corporations across Limburg; small registration fee.',
  },

  // ── Internationaal / expat ──
  {
    name: 'HousingAnywhere',
    url: 'https://housinganywhere.com/nl/',
    cost: 'freemium',
    region: 'internationaal',
    noteNl: 'Internationaal platform; bekijken gratis, reservering loopt via betaalde dienst.',
    noteEn: 'International platform; browsing is free, bookings go via a paid service.',
  },
  {
    name: 'Rentola',
    url: 'https://rentola.nl/',
    cost: 'freemium',
    region: 'internationaal',
    noteNl: 'Internationaal aggregatieplatform; volledige toegang vereist betaald abonnement.',
    noteEn: 'International aggregator; full access requires a paid subscription.',
  },
  {
    name: 'Spotahome',
    url: 'https://www.spotahome.com/nl',
    cost: 'freemium',
    region: 'internationaal',
    noteNl: 'Online bemiddeling met geverifieerde panden; servicekosten bij boeking.',
    noteEn: 'Verified listings via online brokerage; service fee on booking.',
  },
  {
    name: 'Nestpick',
    url: 'https://www.nestpick.com/',
    cost: 'freemium',
    region: 'internationaal',
    noteNl: 'Internationale platformaanbieder voor huurwoningen, vaak gemeubileerd; serviceprijs verschilt.',
    noteEn: 'International rental aggregator, often furnished; service fees vary.',
  },
]
