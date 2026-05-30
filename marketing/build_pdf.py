#!/usr/bin/env python3
"""
Build the Mathlin Booking System marketing one-pager / feature summary as a PDF.
Pure-Python, uses fpdf2. Run with the project venv:
    .pdfvenv/bin/python marketing/build_pdf.py
"""
from fpdf import FPDF

# Brand palette (matches the plugin's purple accent)
PURPLE = (116, 19, 220)      # #7413DC
DARK = (26, 26, 46)          # #1a1a2e
MUTED = (90, 90, 110)
LIGHT_BG = (245, 240, 255)
RULE = (224, 208, 240)
GREEN = (6, 95, 70)

PAGE_W = 210
MARGIN = 18
CONTENT_W = PAGE_W - 2 * MARGIN


class MBSDoc(FPDF):
    def header(self):
        # No header on the cover page
        if self.page_no() == 1:
            return
        self.set_y(8)
        self.set_font("Helvetica", "", 8)
        self.set_text_color(*MUTED)
        self.cell(0, 6, "Mathlin Booking System  -  Feature Summary", align="L")
        self.cell(0, 6, "v3.12.0", align="R")
        self.ln(4)
        self.set_draw_color(*RULE)
        self.set_line_width(0.3)
        self.line(MARGIN, self.get_y(), PAGE_W - MARGIN, self.get_y())
        self.ln(4)

    def footer(self):
        if self.page_no() == 1:
            return
        self.set_y(-15)
        self.set_font("Helvetica", "", 8)
        self.set_text_color(*MUTED)
        self.cell(0, 10, "Built by a Scout Group, for Scout Groups.", align="L")
        self.cell(0, 10, f"Page {self.page_no() - 1}", align="R")


def clean(text: str) -> str:
    """Replace characters outside latin-1 (core fonts only support latin-1)."""
    repl = {
        "\u2019": "'", "\u2018": "'", "\u201c": '"', "\u201d": '"',
        "\u2014": "-", "\u2013": "-", "\u2026": "...", "\u00a3": "GBP ",
        "\u2192": "->", "\u2018": "'",
    }
    for k, v in repl.items():
        text = text.replace(k, v)
    return text.encode("latin-1", "replace").decode("latin-1")


def h1(pdf, text):
    pdf.set_font("Helvetica", "B", 18)
    pdf.set_text_color(*PURPLE)
    pdf.multi_cell(CONTENT_W, 8, clean(text))
    pdf.ln(2)


def h2(pdf, text):
    if pdf.get_y() > 250:
        pdf.add_page()
    pdf.ln(3)
    pdf.set_font("Helvetica", "B", 13)
    pdf.set_text_color(*DARK)
    pdf.multi_cell(CONTENT_W, 7, clean(text))
    pdf.set_draw_color(*PURPLE)
    pdf.set_line_width(0.6)
    y = pdf.get_y() + 0.5
    pdf.line(MARGIN, y, MARGIN + 28, y)
    pdf.ln(3)


def h3(pdf, text):
    if pdf.get_y() > 255:
        pdf.add_page()
    pdf.ln(1)
    pdf.set_font("Helvetica", "B", 11)
    pdf.set_text_color(*PURPLE)
    pdf.multi_cell(CONTENT_W, 6, clean(text))
    pdf.ln(0.5)


def body(pdf, text):
    pdf.set_font("Helvetica", "", 10)
    pdf.set_text_color(*DARK)
    pdf.multi_cell(CONTENT_W, 5.2, clean(text))
    pdf.ln(1.5)


def bullet(pdf, label, text):
    if pdf.get_y() > 265:
        pdf.add_page()
    pdf.set_x(MARGIN + 2)
    pdf.set_font("Helvetica", "B", 10)
    pdf.set_text_color(*PURPLE)
    pdf.cell(4, 5.2, clean("\u2022"))
    start_x = MARGIN + 6
    pdf.set_x(start_x)
    # bold label run, then normal text, wrapped together
    pdf.set_text_color(*DARK)
    if label:
        pdf.set_font("Helvetica", "B", 10)
        pdf.write(5.2, clean(label + " "))
    pdf.set_font("Helvetica", "", 10)
    pdf.write(5.2, clean(text))
    pdf.ln(6.2)


def callout(pdf, text):
    pdf.ln(2)
    pdf.set_fill_color(*LIGHT_BG)
    pdf.set_draw_color(*PURPLE)
    pdf.set_text_color(*DARK)
    pdf.set_font("Helvetica", "I", 10)
    x, y = MARGIN, pdf.get_y()
    pdf.multi_cell(CONTENT_W, 5.5, clean(text), border=0, fill=True)
    pdf.ln(2)


def build():
    pdf = MBSDoc(format="A4")
    pdf.set_auto_page_break(auto=True, margin=18)
    pdf.set_margins(MARGIN, MARGIN, MARGIN)

    # ---------- COVER ----------
    pdf.add_page()
    pdf.set_fill_color(*PURPLE)
    pdf.rect(0, 0, PAGE_W, 95, "F")
    pdf.set_y(34)
    pdf.set_font("Helvetica", "B", 30)
    pdf.set_text_color(255, 255, 255)
    pdf.multi_cell(CONTENT_W, 12, "Mathlin Booking System", align="C")
    pdf.ln(2)
    pdf.set_font("Helvetica", "", 14)
    pdf.multi_cell(CONTENT_W, 7,
                   clean("Turn your Scout HQ into a self-running revenue engine"),
                   align="C")
    pdf.ln(40)
    pdf.set_font("Helvetica", "B", 13)
    pdf.set_text_color(*PURPLE)
    pdf.multi_cell(CONTENT_W, 7, "Feature Summary & Competitor Comparison", align="C")
    pdf.set_font("Helvetica", "", 10)
    pdf.set_text_color(*MUTED)
    pdf.multi_cell(CONTENT_W, 6,
                   clean("Version 3.12.0  -  For Scout Group Lead Volunteers, "
                         "Executive Committees & Treasurers"), align="C")
    pdf.ln(20)
    pdf.set_font("Helvetica", "I", 11)
    pdf.set_text_color(*DARK)
    pdf.multi_cell(CONTENT_W, 6,
                   clean("Built by a Scout Group, for Scout Groups - so that "
                         "better-run buildings can mean better-funded Scouting."),
                   align="C")

    # ---------- PART 1 ----------
    pdf.add_page()
    h1(pdf, "Part 1 - Why MBS Exists")
    body(pdf, "Every Scout Group sits on a valuable asset: its headquarters. Hall hire "
              "is one of the most reliable ways to subsidise camps, equipment, and the "
              "activities that matter to young people. But there's a catch every Group "
              "knows too well: commercial administration eats volunteer time.")
    body(pdf, "Chasing unpaid invoices. Manually switching the heating on before a hirer "
              "arrives. Texting keysafe codes the night before. Juggling council purchase "
              "orders that don't fit a 'pay now' card link. Keeping the regular Beavers and "
              "Cubs nights from cluttering up your booking records. None of this is why "
              "people volunteer for Scouting, and all of it is why halls get under-let.")
    body(pdf, "Mathlin Booking System automates the entire commercial operation of a Scout "
              "HQ so your volunteers can focus on the young people, not the paperwork. It "
              "takes a booking from first enquiry through payment, access, heating and "
              "reconciliation, with little to no human intervention. The result is more "
              "hall income, fewer hours lost, and a professional experience that makes your "
              "Group look every bit as organised as a commercial venue.")
    callout(pdf, "It is built by a Scout Group, for Scout Groups.")

    # ---------- PART 2 ----------
    h1(pdf, "Part 2 - The MBS Arsenal")

    h3(pdf, "Automated Finances")
    bullet(pdf, "Deposit-based bookings", "via WooCommerce - a configurable deposit (e.g. 25%) with the balance due a set number of days before the event.")
    bullet(pdf, "Automatic balance chasing", "- a three-stage escalating reminder sequence plus deposit-balance reminders, sent without anyone lifting a finger.")
    bullet(pdf, "Custom Pricing Tiers", "- charge commercial hirers a premium and offer community or charity groups a discount automatically, with optional per-space rates.")
    bullet(pdf, "Real invoices", "generated and attached to confirmation emails, with financial-year revenue tracking (April-March) built in.")
    bullet(pdf, "Accounting export", "for Xero, Sage and QuickBooks.")

    h3(pdf, "B2B & Council Ready")
    bullet(pdf, "Tier-based offline invoicing.", "Councils and corporate hirers rarely pay by card - they pay by BACS or purchase order. MBS recognises these hirers and automatically swaps the 'Pay Now' card links for professional BACS / PO instructions across every email.")
    bullet(pdf, "Gentle, professional reminders.", "Trusted accounts are exempt from the standard chaser sequence and instead receive a single courteous statement reminder addressed to their finance department.")

    h3(pdf, "Smart Building Integration")
    bullet(pdf, "Native Home Assistant integration", "via real-time webhooks and a scheduled polling component. Your building reacts to its own bookings.")
    bullet(pdf, "Automatic climate control", "- fire the heating or AC a configurable window before each booking and shut it down afterwards, with smart gap-detection so back-to-back bookings don't trigger wasteful cycles.")
    bullet(pdf, "Automated keysafe access codes", "emailed a set number of hours before the event, intelligently gated by payment status or trusted pricing tier. Cancellations and refunds automatically revoke access.")

    h3(pdf, "Scout-First Operations")
    bullet(pdf, "'Scout Nights' bulk booking engine", "- generate a full term or year of recurring section meetings in seconds.")
    bullet(pdf, "Bulk edit, extend, cancel, reopen and delete", "entire series, preserving past records while updating future dates and skipping any week that would clash with a paying hirer.")
    bullet(pdf, "Clean commercial metrics", "- internal Scout bookings block the calendar but are excluded from revenue dashboards and counters, so reporting reflects real income.")

    h3(pdf, "Zero-Conflict Architecture")
    bullet(pdf, "Parent/child space bundling.", "Book the 'Whole HQ' and the 'Main Hall' and 'Meeting Room' are automatically locked, and vice versa.")
    bullet(pdf, "Race-condition-safe booking", "creation, so two people booking at the same moment can never both succeed.")
    bullet(pdf, "Complete searchable audit log", "recording who did what and when, across every booking and recurring series.")

    # ---------- PART 3 ----------
    pdf.add_page()
    h1(pdf, "Part 3 - MBS vs. The Rest")
    body(pdf, "We respect the other tools in this space. But for a Scout Group that owns "
              "its building and wants to run it professionally, the comparison is clear.")

    h2(pdf, "MBS vs. Hallmaster")
    body(pdf, "Hallmaster is a capable, general-purpose hall-booking system - but it's a "
              "generic, subscription-based, hosted product. You rent access year after "
              "year and fit your Group around its feature set.")
    comparison_table(pdf,
        ["", "Mathlin Booking System", "Hallmaster"],
        [
            ["Ownership", "Self-hosted asset you control", "Third-party hosted service"],
            ["Cost model", "No licence fee to MBS itself", "Recurring annual subscription"],
            ["Smart building", "Native Home Assistant automation", "None"],
            ["Scout-specific", "Scout Nights + filtered metrics", "Generic, not Scout-aware"],
            ["B2B invoicing", "Automatic BACS/PO routing", "Generic invoicing"],
            ["Extensibility", "Open WordPress plugin", "Closed platform"],
        ])
    body(pdf, "The headline difference: MBS is an asset you own and control, living on your "
              "own website, with smart-home automation and Scout-specific intelligence that "
              "a generic hosted product isn't built to offer.")

    h2(pdf, "MBS vs. Online Scout Manager (OSM)")
    body(pdf, "Let's be unambiguous: OSM is the undisputed king of youth data, programme "
              "planning and badge tracking. If you run a section, you use OSM, and you "
              "should. We are not trying to replace it.")
    body(pdf, "But OSM is a youth-management system, not a commercial venue-management "
              "platform. It was never designed to take a card deposit from a wedding party, "
              "chase a council's purchase order, switch on the hall heating, or release a "
              "keysafe code to a Saturday hirer.")
    callout(pdf, "MBS handles the building and the public revenue. OSM handles the young "
                 "people and their journey. They run perfectly side by side - and MBS can "
                 "even push financial records to OSM.")

    # ---------- BOTTOM LINE ----------
    h1(pdf, "The Bottom Line")
    body(pdf, "A Scout HQ is too valuable to be managed by hand. Every unbilled hire, every "
              "cold-hall complaint, every hour a volunteer spends chasing a small invoice is "
              "a tax on your Group's mission.")
    body(pdf, "Mathlin Booking System removes that tax. It turns your headquarters into a "
              "professional, automated, revenue-generating venue that effectively runs "
              "itself - freeing your volunteers to do what they joined Scouting to do.")

    pdf.ln(3)
    pdf.set_font("Helvetica", "B", 15)
    pdf.set_text_color(*PURPLE)
    pdf.multi_cell(CONTENT_W, 8, "Upgrade your HQ. Fund your adventures.")
    pdf.ln(1)
    pdf.set_font("Helvetica", "", 10)
    pdf.set_text_color(*DARK)
    body(pdf, "MBS is a self-hosted WordPress plugin with automatic updates and full Home "
              "Assistant integration. If your Group has a website and a hall worth letting, "
              "you have everything you need to begin.")

    pdf.set_font("Helvetica", "I", 8)
    pdf.set_text_color(*MUTED)
    body(pdf, "Note: MBS itself carries no licence fee. Groups provide their own WordPress "
              "hosting and a payment gateway (e.g. Stripe/PayPal), which apply their usual "
              "transaction fees.")

    out = "marketing/Mathlin-Booking-System-Feature-Summary.pdf"
    pdf.output(out)
    print("Wrote", out)


def comparison_table(pdf, headers, rows):
    col_w = [38, 78, 70]
    line_h = 6
    # header
    pdf.set_font("Helvetica", "B", 9.5)
    pdf.set_fill_color(*PURPLE)
    pdf.set_text_color(255, 255, 255)
    for w, txt in zip(col_w, headers):
        pdf.cell(w, 8, clean(txt), border=0, fill=True, align="L")
    pdf.ln(8)
    # rows
    pdf.set_text_color(*DARK)
    fill = False
    for row in rows:
        # compute height from wrapped cells
        pdf.set_font("Helvetica", "", 9)
        heights = []
        for w, txt in zip(col_w, row):
            n = pdf.multi_cell(w, line_h, clean(txt), dry_run=True, output="LINES")
            heights.append(len(n) * line_h)
        rh = max(heights)
        if pdf.get_y() + rh > 275:
            pdf.add_page()
        x0, y0 = MARGIN, pdf.get_y()
        if fill:
            pdf.set_fill_color(*LIGHT_BG)
        else:
            pdf.set_fill_color(255, 255, 255)
        x = x0
        for i, (w, txt) in enumerate(zip(col_w, row)):
            pdf.rect(x, y0, w, rh, "F" if fill else "")
            pdf.set_xy(x, y0)
            pdf.set_font("Helvetica", "B" if i == 0 else "", 9)
            pdf.multi_cell(w, line_h, clean(txt), align="L")
            x += w
        pdf.set_xy(x0, y0 + rh)
        fill = not fill
    # bottom rule
    pdf.set_draw_color(*RULE)
    pdf.set_line_width(0.3)
    pdf.line(MARGIN, pdf.get_y(), MARGIN + sum(col_w), pdf.get_y())
    pdf.ln(3)


if __name__ == "__main__":
    build()
