#!/usr/bin/env python3
"""
Regenerate data/nl_rent_benchmarks.php from data/wiki_nl_gemeenten_lijst.json
(Wikipedia API parse of 'Lijst van Nederlandse gemeenten', stand 2024).
"""

from __future__ import annotations

import json
import re
import sys
from collections import Counter
from pathlib import Path
import html as html_module

ROOT = Path(__file__).resolve().parents[1]
DATA_JSON = ROOT / "data" / "wiki_nl_gemeenten_lijst.json"
OUT_PHP = ROOT / "data" / "nl_rent_benchmarks.php"

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


def main() -> int:
    src = DATA_JSON if len(sys.argv) < 2 else Path(sys.argv[1])
    j = json.loads(src.read_text(encoding="utf-8"))
    htmltext = j["parse"]["text"]["*"]
    rows = parse_rows(htmltext)
    if len(rows) < 340:
        print(f"Warning: expected ~345 rows, got {len(rows)}", file=sys.stderr)

    benchmarks = build_benchmarks(rows)
    aliases = dict(EXTRA_ALIASES)

    lines = [
        "<?php",
        "",
        "/**",
        " * Nederlandse gemeenten (Wikipedia-lijst) + indicatieve €/maand benchmarks.",
        " * Gegenereerd door scripts/generate_nl_rent_benchmarks.py — niet handmatig bewerken.",
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
    lines.append("    'aliases' => [")

    for k in sorted(aliases.keys()):
        lines.append(f"        {php_squote(k)} => {php_squote(aliases[k])},")

    lines.append("    ],")
    lines.append("];")
    lines.append("")

    OUT_PHP.write_text("\n".join(lines), encoding="utf-8")
    print(f"Wrote {OUT_PHP} ({len(benchmarks)} benchmarks, {len(aliases)} aliases)")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
