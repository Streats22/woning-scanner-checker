#!/usr/bin/env python3
r"""
Regenerate data/nl_rent_benchmarks.php from data/wiki_nl_gemeenten_lijst.json
(Wikipedia API parse of 'Lijst van Nederlandse gemeenten', stand 2024).

Marktijking (stand oktober 2026):
- €/m² per stad uit de Pararius Huurmonitor Q3 2026 (vrije sector, nieuwe huurders).
- Model-maandhuur = €/m² × REFERENCE_SURFACE_M2 (gemiddeld appartement; zelfde constante als
  App\Support\RentPerSquareMeterModel).
- Gemeenten zonder eigen Pararius-cijfer: de oude provincie-defaults/handmatige waarden, geschaald met
  de mediane verhouding (Pararius-model / oude waarde) van de gemeten steden.
- Kamerhuur (mediaan) uit de Kamernet-huurrapportage Q2 2026 (Q1 waar Q2 ontbrak); elders afgeleid
  met de mediane verhouding kamer / hele woning.
Bronnen en onderbouwing: /mnt/project-files/onderzoek (rapport "Huurmarkt en huurfraude 2026").
"""

from __future__ import annotations

import json
import re
import sys
from collections import Counter
from pathlib import Path
import html as html_module
from statistics import median

ROOT = Path(__file__).resolve().parents[1]
DATA_JSON = ROOT / "data" / "wiki_nl_gemeenten_lijst.json"
OUT_PHP = ROOT / "data" / "nl_rent_benchmarks.php"

# m² — referentie-oppervlakte voor de model-maandhuur (moet gelijk zijn aan RentPerSquareMeterModel).
REFERENCE_SURFACE_M2 = 65

# Pararius Huurmonitor Q3 2026 (gepubliceerd 7 okt 2026), €/m²/maand vrije sector.
# 's-Hertogenbosch: Q1 2026 (geen Q3-cijfer gepubliceerd).
PARARIUS_PER_M2: dict[str, float] = {
    "Amsterdam": 28.56,
    "Amstelveen": 24.72,
    "Haarlem": 24.34,
    "Leiden": 23.45,
    "Den Haag": 22.95,
    "Rotterdam": 22.92,
    "Utrecht": 22.87,
    "Delft": 22.49,
    "'s-Hertogenbosch": 20.24,
    "Eindhoven": 19.61,
    "Nijmegen": 19.36,
    "Groningen": 19.05,
    "Maastricht": 18.87,
    "Breda": 18.75,
    "Almere": 18.69,
    "Amersfoort": 17.81,
    "Tilburg": 17.64,
    "Arnhem": 17.24,
    "Enschede": 15.98,
    "Apeldoorn": 15.70,
    "Helmond": 15.31,
    "Leeuwarden": 14.33,
    "Zwolle": 14.32,
}

# Kamernet-huurrapportage Q2 2026 (mediaan €/maand); Rotterdam en Groningen: Q1 2026.
# Utrecht: gemiddelde van de twee gepubliceerde waarden (€ 755 / € 775).
KAMERNET_ROOM_RENT: dict[str, int] = {
    "Amsterdam": 950,
    "Utrecht": 765,
    "Haarlem": 750,
    "Den Haag": 745,
    "Rotterdam": 710,
    "Tilburg": 595,
    "Groningen": 580,
    "Maastricht": 550,
    "Eindhoven": 540,
    "Leeuwarden": 465,
    "Ede": 455,
    "Enschede": 388,
}

# Indicatieve provincie-defaults (€/maand model vrije sector, zelfde orde als eerdere handmatige map).
PROVINCE_DEFAULTS: dict[str, int] = {
    "Noord-Holland": 1300,
    "Zuid-Holland": 1250,
    "Utrecht": 1300,
    "Noord-Brabant": 1100,
    "Gelderland": 1050,
    "Limburg": 1000,
    "Overijssel": 1000,
    "Groningen": 1000,
    "Friesland": 950,
    "Drenthe": 900,
    "Flevoland": 1100,
    "Zeeland": 1000,
    "Caribisch Nederland": 1150,
}

# Handmatige scherpstellingen (€/maand), overgenomen uit de oude RentBenchmarkMap.
MANUAL_OVERRIDES: dict[str, int] = {
    "Amsterdam": 1850,
    "Rotterdam": 1450,
    "Den Haag": 1400,
    "Utrecht": 1650,
    "Haarlem": 1550,
    "Haarlemmermeer": 1550,
    "Zaanstad": 1500,
    "Almere": 1350,
    "Amstelveen": 1650,
    "Hilversum": 1400,
    "Leiden": 1450,
    "Delft": 1450,
    "Gouda": 1250,
    "Zoetermeer": 1250,
    "Schiedam": 1300,
    "Vlaardingen": 1150,
    "Capelle aan den IJssel": 1300,
    "Purmerend": 1350,
    "Hoofddorp": 1600,
    "Alkmaar": 1200,
    "Hoorn": 1150,
    "Katwijk": 1300,
    "Leidschendam-Voorburg": 1450,
    "Dordrecht": 1150,
    "Barendrecht": 1250,
    "Ridderkerk": 1250,
    "Eindhoven": 1300,
    "Tilburg": 1150,
    "Breda": 1250,
    "'s-Hertogenbosch": 1200,
    "Helmond": 1000,
    "Oss": 1000,
    "Roosendaal": 1050,
    "Bergen op Zoom": 1000,
    "Veldhoven": 1250,
    "Arnhem": 1100,
    "Nijmegen": 1150,
    "Apeldoorn": 1000,
    "Ede": 1100,
    "Doetinchem": 950,
    "Harderwijk": 1050,
    "Barneveld": 1000,
    "Culemborg": 1150,
    "Amersfoort": 1300,
    "Zeist": 1400,
    "Veenendaal": 1050,
    "Houten": 1350,
    "Castricum": 1200,
    "Dijk en Waard": 1150,
    "Maastricht": 1200,
    "Venlo": 950,
    "Heerlen": 950,
    "Sittard-Geleen": 950,
    "Roermond": 1000,
    "Enschede": 1050,
    "Zwolle": 1100,
    "Hengelo": 1000,
    "Almelo": 950,
    "Deventer": 1050,
    "Zutphen": 1000,
    "Groningen": 1150,
    "Assen": 950,
    "Emmen": 850,
    "Smallingerland": 900,
    "Leeuwarden": 950,
    "Heerenveen": 950,
    "Lelystad": 1050,
    "Dronten": 950,
    "Middelburg": 1000,
    "Vlissingen": 1000,
    "Goes": 1000,
    "IJsselstein": 1250,
    "Rijswijk": 1350,
    "Wassenaar": 1500,
    "Nieuwegein": 1300,
    "Utrechtse Heuvelrug": 1200,
    "Soest": 1250,
    "Maassluis": 1200,
    "Pijnacker-Nootdorp": 1350,
    "Westland": 1250,
    "Krimpen aan den IJssel": 1200,
    "Papendrecht": 1150,
    "Sliedrecht": 1100,
    "Gorinchem": 1100,
    "Maashorst": 1050,
    "Weert": 1000,
    "Kerkrade": 900,
    "Landgraaf": 900,
    "Stein": 950,
    "Beek": 1000,
    "Hoensbroek": 950,
    "Hoogeveen": 900,
    "Meppel": 950,
    "Coevorden": 900,
    "Stadskanaal": 850,
    "Veendam": 850,
    "Terneuzen": 950,
    "Hulst": 900,
    "Voorne aan Zee": 1150,
}

# Aliassen: plaatsnaam/oude gemeente → canonieke sleutel in benchmarks (huidige gemeente).
EXTRA_ALIASES: dict[str, str] = {
    "Den Bosch": "'s-Hertogenbosch",
    "The Hague": "Den Haag",
    "'s-Gravenhage": "Den Haag",
    "Bergen L": "Bergen (Limburg)",
    "Bergen NH": "Bergen (Noord-Holland)",
    "Spijkenisse": "Voorne aan Zee",
    "Hellevoetsluis": "Voorne aan Zee",
    "Hoofddorp": "Haarlemmermeer",
    "Heerhugowaard": "Dijk en Waard",
    "Hoensbroek": "Heerlen",
    "Drachten": "Smallingerland",
    "Uden": "Maashorst",
    "Veghel": "Maashorst",
    "Sittard": "Sittard-Geleen",
    # Voormalige kernen / zoektermen → huidige gemeente (Gooise Meren sinds 2016)
    "Bussum": "Gooise Meren",
    "Naarden": "Gooise Meren",
}


def norm_prov(title: str) -> str:
    t = html_module.unescape(title).replace("&#39;", "'")
    t = re.sub(r"\s*\(Nederlandse provincie\)\s*$", "", t)
    t = re.sub(r"\s*\(provincie\)\s*$", "", t)
    return t.strip()


def parse_rows(htmltext: str) -> list[tuple[str, str, str]]:
    start = htmltext.find('<table class="wikitable')
    end = htmltext.find("</table>", start)
    tbl = htmltext[start:end]
    trs = re.findall(r"<tr[^>]*>(.*?)</tr>", tbl, re.DOTALL)
    rows: list[tuple[str, str, str]] = []
    for tr in trs[1:]:
        tds = re.findall(r"<t[dh][^>]*>(.*?)</t[dh]>", tr, re.DOTALL)
        if len(tds) < 4:
            continue
        inner, href = first_gemeente_link(tds[0])
        prov_cell = tds[3]
        pm = re.search(r'<a href="/wiki/[^"]+" title="([^"]+)">', prov_cell)
        prov = norm_prov(pm.group(1)) if pm else None
        if inner and prov and href:
            rows.append((inner, href, prov))
    return rows


def first_gemeente_link(th_html: str) -> tuple[str | None, str | None]:
    for m in re.finditer(
        r'<a href="/wiki/([^"]+)"(?:\s+title="([^"]+)")?[^>]*>([^<]*)</a>', th_html
    ):
        href = m.group(1)
        if href.startswith("Bestand:") or href.startswith("Wikipedia:"):
            continue
        title = m.group(2)
        text = html_module.unescape(m.group(3)).strip()
        inner = text if text else (html_module.unescape(title) if title else "")
        return inner, href
    return None, None


def canonical_key(inner: str, prov: str, cnt: Counter) -> str:
    if cnt[inner] > 1:
        return f"{inner} ({prov})"
    return inner


def php_squote(s: str) -> str:
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def build_benchmarks(rows: list[tuple[str, str, str]]) -> dict[str, int]:
    cnt = Counter(i for i, _, _ in rows)
    benchmarks: dict[str, int] = {}
    for inner, _href, prov in rows:
        key = canonical_key(inner, prov, cnt)
        if prov not in PROVINCE_DEFAULTS:
            raise ValueError(f"Unknown province: {prov!r}")
        benchmarks[key] = PROVINCE_DEFAULTS[prov]

    # Handmatige benchmarks (canonieke gemeentenaam; geen alias-keys)
    for name, amount in MANUAL_OVERRIDES.items():
        if name in benchmarks:
            benchmarks[name] = amount

    return benchmarks


def round_to(value: float, step: int) -> int:
    return int(round(value / step) * step)


def calibrate(legacy: dict[str, int]) -> tuple[dict[str, int], dict[str, float], dict[str, int], float, float]:
    """Zet oude modelwaarden om naar marktstand Q3 2026 (zie module-docstring)."""
    for city in list(PARARIUS_PER_M2) + list(KAMERNET_ROOM_RENT):
        if city not in legacy:
            raise ValueError(f"Unknown gemeente in market data: {city!r}")

    market_factor = median(
        per_m2 * REFERENCE_SURFACE_M2 / legacy[city] for city, per_m2 in PARARIUS_PER_M2.items()
    )

    per_m2: dict[str, float] = {}
    monthly: dict[str, int] = {}
    for city, old in legacy.items():
        value = PARARIUS_PER_M2.get(city, old * market_factor / REFERENCE_SURFACE_M2)
        per_m2[city] = round(value, 2)
        monthly[city] = round_to(value * REFERENCE_SURFACE_M2, 10)

    room_factor = median(rent / monthly[city] for city, rent in KAMERNET_ROOM_RENT.items())
    rooms = {
        city: KAMERNET_ROOM_RENT.get(city, round_to(monthly[city] * room_factor, 5))
        for city in legacy
    }

    return monthly, per_m2, rooms, market_factor, room_factor


def main() -> int:
    src = DATA_JSON if len(sys.argv) < 2 else Path(sys.argv[1])
    j = json.loads(src.read_text(encoding="utf-8"))
    htmltext = j["parse"]["text"]["*"]
    rows = parse_rows(htmltext)
    if len(rows) < 340:
        print(f"Warning: expected ~345 rows, got {len(rows)}", file=sys.stderr)

    benchmarks, per_m2, rooms, market_factor, room_factor = calibrate(build_benchmarks(rows))
    aliases = dict(EXTRA_ALIASES)

    lines = [
        "<?php",
        "",
        "/**",
        " * Nederlandse gemeenten (Wikipedia-lijst) + indicatieve €/maand benchmarks.",
        " * Gegenereerd door scripts/generate_nl_rent_benchmarks.py — niet handmatig bewerken.",
        " *",
        " * benchmarks: model-€/maand (€/m² × " + str(REFERENCE_SURFACE_M2) + " m²), per_m2: €/m²/maand vrije sector,",
        " * rooms: mediane kamerhuur €/maand. Marktstand: Pararius Q3 2026, Kamernet Q2 2026.",
        f" * Marktfactor overige gemeenten: {market_factor:.3f}; kamer/hele woning: {room_factor:.3f}.",
        " */",
        "",
        "declare(strict_types=1);",
        "",
        "return [",
        "    'benchmarks' => [",
    ]

    for k in sorted(benchmarks.keys()):
        lines.append(f"        {php_squote(k)} => {benchmarks[k]},")

    lines.append("    ],")
    lines.append("    'per_m2' => [")

    for k in sorted(per_m2.keys()):
        lines.append(f"        {php_squote(k)} => {per_m2[k]:.2f},")

    lines.append("    ],")
    lines.append("    'rooms' => [")

    for k in sorted(rooms.keys()):
        lines.append(f"        {php_squote(k)} => {rooms[k]},")

    lines.append("    ],")
    lines.append("    'aliases' => [")

    for k in sorted(aliases.keys()):
        lines.append(f"        {php_squote(k)} => {php_squote(aliases[k])},")

    lines.append("    ],")
    lines.append("];")
    lines.append("")

    OUT_PHP.write_text("\n".join(lines), encoding="utf-8")
    print(
        f"Wrote {OUT_PHP} ({len(benchmarks)} benchmarks, {len(aliases)} aliases, "
        f"market factor {market_factor:.3f}, room factor {room_factor:.3f})"
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
