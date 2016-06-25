package com.isbtplanner.front;

import java.util.List;

import org.openqa.selenium.By;
import org.testng.annotations.Test;

import com.isbtplanner.Mail;

public class HomeTest extends BaseTest {

	@Test
	/**
	 * Test the home page
	 */
	public void homeTest() {
		goUrl("index.html");

		// fill in contact form
		driver.findElement(By.name("name")).sendKeys("Test person");
		driver.findElement(By.name("email")).sendKeys("test@example.org");
		driver.findElement(By.name("phone")).sendKeys("+31(0)6-1234 5678");
		driver.findElement(By.name("newsletter")).click();
		driver.findElement(By.name("message")).sendKeys(
				"This is the message of this email");
		driver.findElement(By.name("name")).submit();

		// check send email
		openProfiler();
		List<Mail> mails = getMails();
		mails.get(0).assertSubject("ISBT planner automatic mailing");
		String message = "On the contact page, someone has filled in this form: \n"
				+ "Name: Test person\n"
				+ "Email: test@example.org\n"
				+ "Phone: +31(0)6-1234 5678\n"
				+ "Newsletter: on\n"
				+ "Message: This is the message of this email\n"
				+ "\n"
				+ "---End of message---";
		mails.get(0).assertMessage(message);

	}
}