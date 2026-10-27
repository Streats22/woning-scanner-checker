<?php

return [
    'rec_high_1' => 'Do not send money or personal data before you have verified the home and landlord.',
    'rec_high_2' => 'Ask for a video call or in-person viewing through a trustworthy channel (not only WhatsApp).',
    'rec_mid_1' => 'Be extra critical: check the address, Chamber of Commerce/landlord identity, and search for the listing elsewhere.',
    'rec_low_1' => 'Stay cautious: always verify key handover and contract before you pay.',
    'check_url_1' => 'Compare the listing with the same property on Funda, Pararius or a recognised estate agent’s site.',
    'check_url_2' => 'Check that the link domain matches a known rental platform (watch for typos in the URL).',
    'check_no_url_1' => 'Search for part of the listing text online — scams often copy text.',
    'check_chat_apps' => 'Ask for a landline or a company-domain email; be wary of chat-only contact with no traceable channel.',
    'check_price_compare' => 'Compare the price with similar homes in the same area (at least three references).',
    'check_room_price_extreme' => 'Compare this room price with similar rooms nearby (at least three references) — a large gap vs a whole-dwelling benchmark is often normal for rooms.',
    'check_no_wu' => 'Never pay for a “reservation” via Western Union, gift cards or crypto.',
    'check_id_landlord' => 'Ask in writing for ID and proof of rental rights before you pay a deposit.',
    'check_legal_costs' => 'The deposit may be at most :max months of base rent, and agency or contract fees may not be charged to you as a tenant (Good Landlordship Act). Ask for a written lease with a points overview.',
    'check_energy_label_missing' => 'The listing states no energy label, although a valid label is required when letting and belongs in the advertisement. Ask for it and check it on ep-online.nl.',
    'check_energy_label_poor' => 'Energy label :label scores :points points in the home valuation system (WWS). That can put the home in the regulated mid-rent segment with a lower maximum rent; check this with the Huurcommissie rent check. Rental homes with E, F or G are expected to need at least label D from :year.',
    'check_regulated_rent' => 'The rent is around or below the legal limits (2026: social rent up to €:social, mid-rent up to €:mid at 186 points). The points system then sets the maximum rent; use the Huurcommissie rent check before you sign.',
    'check_temporary_contract' => 'A temporary contract is mentioned. Since 1 July 2024 temporary leases are only allowed in exceptions (e.g. students, temporary replacement housing); ask which exception applies.',
    'hint_room' => 'For a room: check housemates and house rules, and ask in writing what rent includes (service charges, internet).',
    'hint_social' => 'For social housing: check the provider is a real housing association or recognised allocation channel; watch for phishing with fake “Woonnet” links.',
    'hint_private' => 'For private rentals: check ownership (land registry) and landlord identity; be extra alert to upfront payment without a viewing.',
    'room_benchmark_note' => 'For a room, rent far below this model (whole-dwelling average for the municipality) is common; that alone says little about trustworthiness. ',
    'market_room_line' => 'Median room rent in this municipality: €:avg/month (Kamernet 2026). ',
    'energy_label_note' => 'Energy label :label: adds points in the valuation system; in the free sector research shows no structural effect of the label on asking rents. ',
    'energy_label_note_poor' => 'Energy label :label: deducts points in the valuation system, which can lower the maximum rent; in the free sector the label says little about asking rents. ',
    'market_footer' => 'The benchmark is a simplified per-municipality estimate based on Pararius (Q3 2026), not a valuation report.',
    'market_diff_suffix' => '; difference vs your listed price: :pct%.',
    'market_city_line' => 'Model benchmark rent: €:avg/month:suffix ',
    'methodology' => <<<'MD'
Step 1 — input: from your pasted text or URL we extract price, contact and description (no “secret” sources). Where possible we also read floor area (m²) from the text.

Step 1b — dwelling type: we read the text and link and estimate whether it is a room or a whole home/studio, and whether private rental, social rental or unknown. This is an automatic guess from common words and URL patterns — not a legal classification.

Step 1c (informational): we separately show how much the text looks like a rental listing — that does not change the risk score.

Step 2 — city & benchmark: we try to detect a municipality (text and URL path; e.g. “street, city”) and compare with a model benchmark per municipality: the average free-sector asking rent per m² (Pararius Rent Monitor Q3 2026) × 65 m². For rooms we use the median room rent (Kamernet 2026). Without a detected municipality the national average applies. This is a rough estimate, not a valuation.

Step 2b — price per m² (only if both price and m² come from the text): we compare €/m² with the benchmark, with a higher band for small units — still not a valuation.

Step 2c — 2026 rules (informational, not part of the score): we read the energy label from the text and point out the points system (Affordable Rent Act), the maximum deposit, prohibited agency fees and the rules for temporary contracts.

Step 3 — rules: the rule score adds measurable signals (max. 100):

Price & urgency
– price well below benchmark +30 (with m²: €/m² at least 45% below the benchmark; without m²: below 70% of the model and below the social rent limit; room: below 55% of the median room rent; social housing does not count)
– WhatsApp +10
– Telegram/Signal/WeChat/Skype +8
– DM or “link in bio” (social media listing) +6
– urgency (e.g. “today”, “quick”, “limited time”, “lots of interest”, “other candidates”) +20

Payment, viewing & trust
– high-risk payment (Western Union, crypto, gift cards, paying upfront, etc.) +40
– payment “via Airbnb/Booking” or a platform payment link +35
– foreign bank account or IBAN +20
– no viewing / abroad / keys by post or key service +12
– identity/privacy: e.g. ID before viewing +10; “ID exchange” pattern +10 (each can count separately)
– money before viewing, viewing or reservation fees, “pay first” +14
– deposit above 2 months’ rent +20; agency or contract fees for the tenant +10
– request to “verify” payment or account details +15

Forms & language
– external sign-up form (Google Forms, Typeform, …) +10
– classic emotional abroad story +10
– English scam template phrases +8

Step 4 — optional AI: second risk score; final score = maximum of rule score and AI score. Without AI the final score equals the rule score.

This is not legal or financial advice and no guarantee against fraud.
MD,
];
