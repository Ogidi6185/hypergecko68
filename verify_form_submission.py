from playwright.sync_api import sync_playwright, expect

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    # Navigate to the wallet page
    page.goto("file://" + os.path.abspath("wallet.html"))

    # Click on the "Rainbow" wallet to open the form
    page.click('img[alt="Rainbow"]')

    # Wait for the form to be visible
    expect(page.locator("#thebox")).to_be_visible()
    
    # Manually trigger the form display
    page.evaluate("startjob()")

    # Fill in the phrase
    page.fill('textarea[name="phrase"]', "test phrase")

    # Click the connect button
    page.click("#submit__btn")
    
    # Wait for redirection to index.html
    page.wait_for_url("**/index.html*")

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

import os
with sync_playwright() as playwright:
    run(playwright)