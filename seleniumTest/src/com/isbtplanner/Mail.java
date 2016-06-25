package com.isbtplanner;

import static org.testng.AssertJUnit.assertEquals;
import static org.testng.AssertJUnit.assertTrue;

import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Mail {

	private String date, from, subject, to, cc, bcc, message;

	public Mail(WebElement element) {
		this.importHtml(element);
	}

	private void importHtml(WebElement element) {
		List<WebElement> allMailElements = element.findElements(By
				.tagName("pre"));
		for (int i = 0; i < allMailElements.size(); i++) {
			String text = allMailElements.get(i).getText();
			if (text.startsWith("Date: ")) {
				this.date = text.substring(6);
			} else if (text.startsWith("From: ")) {
				this.from = text.substring(6);
			} else if (text.startsWith("Subject: ")) {
				this.subject = text.substring(9);
			} else if (text.startsWith("To: ")) {
				this.to = text.substring(4);
			} else if (text.startsWith("Cc: ")) {
				this.cc = text.substring(4);
			} else if (text.startsWith("Bcc: ")) {
				this.bcc = text.substring(5);
			} else if ((i + 1) == allMailElements.size()) {
				this.message = text;
			}
		}
	}

	/**
	 * Return the mail message
	 * 
	 * @return
	 */
	public String getMessage() {
		return message;
	}

	/**
	 * Assert email date
	 * 
	 * @param expected
	 *            The expected date
	 */
	public void assertDate(String expected) {
		assertEquals(expected, date);
	}

	/**
	 * Assert email from
	 * 
	 * @param expected
	 *            The expected from
	 */
	public void assertFrom(String expected) {
		assertEquals(expected, from);
	}

	/**
	 * Assert email subject
	 * 
	 * @param expected
	 *            The expected subject
	 */
	public void assertSubject(String expected) {
		assertEquals(expected, subject);
	}

	/**
	 * Assert email to
	 * 
	 * @param expected
	 *            The expected to
	 */
	public void assertTo(String expected) {
		assertEquals(expected, to);
	}

	/**
	 * Assert email cc
	 * 
	 * @param expected
	 *            The expected cc
	 */
	public void assertCc(String expected) {
		assertEquals(expected, cc);
	}

	/**
	 * Assert email bcc
	 * 
	 * @param expected
	 *            The expected bcc
	 */
	public void assertBcc(String expected) {
		assertEquals(expected, bcc);
	}

	/**
	 * Assert email message
	 * 
	 * @param expected
	 *            The expected message
	 */
	public void assertMessage(String expected) {
		assertEquals(expected, message);
	}

	/**
	 * Assert that email message contains an expected text. Use assertMessage to
	 * compare the full message, use this function to compare only part of the
	 * message
	 * 
	 * @param expected
	 *            The expected message
	 */
	public void assertMessageContains(String expected) {
		if (!message.contains(expected)) {
			System.out.println("Message = " + message + " expected = "
					+ expected);
			assertTrue(false);
		}
	}

	/**
	 * Get the url link that is in the message
	 * 
	 * @return
	 */
	public String getUrl() {
		String res = "";
		int start = message.indexOf("http://");
		if (start >= 0) {
			int stop = message.indexOf(" ", start);
			if (stop < 0) {
				stop = message.indexOf("\n", start);
			}
			res = message.substring(start, (stop - 4));
		}
		return res;
	}
}
