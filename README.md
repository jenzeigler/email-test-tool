# Email Test Tool

A small WordPress plugin that answers the first question in any "my emails aren't arriving" case: **can WordPress hand off email at all?**

It adds an **Email Test** page under **Tools**. Enter an address, send a test, and the plugin tells you whether WordPress handed the email off successfully. If it failed, it shows the reason WordPress gave and suggests what to check next.

[**▶ Try it live in your browser**](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/jenzeigler/email-test-tool/main/blueprint.json)

The live demo runs in WordPress Playground. It will show a successful handoff, but no email is actually delivered, which is a good example of why a successful handoff doesn’t guarantee delivery.

## Why it exists

When WordPress emails go missing, the first step is figuring out whether the problem is the **handoff** (WordPress can't send at all) or **delivery** (the email leaves but never reaches the inbox). Those need very different fixes. This tool tells you which side of that line you're on in a few seconds.

For what to do next, see the companion guide: [Why Isn't My WordPress Email Sending?](https://github.com/jenzeigler/wordpress-email-troubleshooting)

## Installation

1. Download this repository as a ZIP (**Code**, then **Download ZIP**).
2. In WordPress, go to **Plugins**, then **Add New Plugin**, then **Upload Plugin**.
3. Upload the ZIP and click **Activate**.
4. Go to **Tools**, then **Email Test**.

## How it works

* Uses `wp_mail()`, the same function every WordPress email goes through.
* Listens to the `wp_mail_failed` hook to capture the actual error message when a send fails.
* Matches common errors to a specific next step, so an invalid From address gets different advice than a server that can't send mail at all.
* Only administrators can use it, and the form is protected with a nonce.
* Input is sanitized and all output is escaped.

## Roadmap

* [ ] Custom subject line
* [ ] Advice for SMTP connection errors
* [ ] Show which mailer is active (for example, an SMTP plugin vs. the server default)
* [ ] Log recent test results

## License

GPL-2.0-or-later
