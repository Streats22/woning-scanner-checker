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
    'hint_room' => 'For a room: check housemates and house rules, and ask in writing what rent includes (service charges, internet).',
    'hint_social' => 'For social housing: check the provider is a real housing association or recognised allocation channel; watch for phishing with fake “Woonnet” links.',
    'hint_private' => 'For private rentals: check ownership (land registry) and landlord identity; be extra alert to upfront payment without a viewing.',
    'room_benchmark_note' => 'For a room, rent far below this model (whole-dwelling average for the municipality) is common; that alone says little about trustworthiness. ',
    'market_footer' => 'The benchmark is a simplified estimate per city/region, not a valuation report.',
    'market_diff_suffix' => '; difference vs your listed price: :pct%.',
    'market_city_line' => 'Model benchmark rent: €:avg/month:suffix ',
    'methodology' => <<<'MD'
Step 1 — input: from your pasted text or URL we extract price, contact and description (no “secret” sources). Where possible we also read floor area (m²) from the text.

Step 1b — dwelling type: we read the text and link and estimate whether it is a room or a whole home/studio, and whether private rental, social rental or unknown. This is an automatic guess from common words and URL patterns — not a legal classification.

Step 1c (informational): we separately show how much the text looks like a rental listing — that does not change the risk score.

Step 2 — city & benchmark: we try to detect a municipality (text and URL path; e.g. “street, city”) and compare with a fixed model benchmark (€/month) per municipality; elsewhere a default applies. This is a rough estimate, not a valuation.

Step 2b — price per m² (only if both price and m² come from the text): we show indicative €/m² next to the model and a band that allows higher values for small units — still not a valuation.

Step 3 — rules: the rule score adds measurable signals (max. 100):

Price & urgency
– price well below benchmark +30 (for a room: only when unusually low vs the same model, because rooms are normally far below a whole-dwelling average)
– WhatsApp +10
– Telegram/Signal/WeChat/Skype +8
– urgency (e.g. “today”, “quick”, “limited time”, “lots of interest”) +20

Payment, viewing & trust
– high-risk payment (Western Union, crypto, gift cards, paying upfront, etc.) +40
– no viewing / abroad / keys by post or key service +12
– identity/privacy: e.g. ID before viewing +10; “ID exchange” pattern +10 (each can count separately)
– money before viewing +14

Forms & language
– external sign-up form (Google Forms, Typeform, …) +10
– classic emotional abroad story +10
– English scam template phrases +8

Step 4 — optional AI: second risk score; final score = maximum of rule score and AI score. Without AI the final score equals the rule score.

This is not legal or financial advice and no guarantee against fraud.
MD,
];
