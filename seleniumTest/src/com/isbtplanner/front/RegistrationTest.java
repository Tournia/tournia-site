package com.isbtplanner.front;

import java.util.List;

import org.openqa.selenium.By;
import org.testng.annotations.Test;

import com.isbtplanner.Mail;

public class RegistrationTest extends BaseTest {

	@Test
	/**
	 * Test the registration form of a new user, but test filling in the wrong values
	 */
	public void registerIncorrectFormTest() {
		goUrl("login.html");

		// leave the form empty
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.submit();
		assertTitle("Log in");
		assertContains(By.cssSelector(".signin_box form"),
				"Please enter your name");
		assertContains(By.cssSelector(".signin_box form"),
				"Please enter a username");
		assertContains(By.cssSelector(".signin_box form"),
				"Please enter an email");
		assertContains(By.cssSelector(".signin_box form"),
				"Please enter a password");

		// fill in form wrong
		driver.findElement(By.name("fos_user_registration_form[name]"))
				.sendKeys("");
		driver.findElement(By.name("fos_user_registration_form[username]"))
				.sendKeys("");
		driver.findElement(By.name("fos_user_registration_form[email]"))
				.sendKeys("bogus");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][first]"))
				.sendKeys("pwd1");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.sendKeys("notmatching");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.submit();
		assertTitle("Log in");
		assertContains(By.cssSelector(".signin_box form"),
				"Please enter your name");
		assertContains(By.cssSelector(".signin_box form"),
				"Please enter a username");
		assertContains(By.cssSelector(".signin_box form"),
				"The email is not valid");
		assertContains(By.cssSelector(".signin_box form"),
				"The entered passwords don't match");
	}

	@Test
	/**
	 * Test two times registration of the same user
	 */
	public void registerDoubleTest() {
		goUrl("login.html");

		// fill in form correct
		String username = "selenium" + rand();
		String email = "selenium" + rand() + "@tournia.net";

		driver.findElement(By.name("fos_user_registration_form[name]"))
				.sendKeys("Test Person");
		driver.findElement(By.name("fos_user_registration_form[username]"))
				.sendKeys(username);
		driver.findElement(By.name("fos_user_registration_form[email]"))
				.sendKeys(email);
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][first]"))
				.sendKeys("pwd1");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.sendKeys("pwd1");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.submit();

		assertTitle("Registration email sent");

		// second time filling in the form
		goUrl("login.html");
		driver.findElement(By.name("fos_user_registration_form[name]"))
				.sendKeys("Test Person");
		driver.findElement(By.name("fos_user_registration_form[username]"))
				.sendKeys(username);
		driver.findElement(By.name("fos_user_registration_form[email]"))
				.sendKeys(email);
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][first]"))
				.sendKeys("pwd1");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.sendKeys("pwd1");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.submit();

		assertTitle("Log in");
		assertContains(By.cssSelector(".signin_box form"),
				"The username is already used");
		assertContains(By.cssSelector(".signin_box form"),
				"The email is already used");
	}

	@Test
	/**
	 * Test the registration of a new user
	 */
	public void registerNormalTest() {
		goUrl("login.html");

		// fill in form
		String username = "selenium" + rand();
		String email = "selenium" + rand() + "@tournia.net";

		driver.findElement(By.name("fos_user_registration_form[name]"))
				.sendKeys("Test Person");
		driver.findElement(By.name("fos_user_registration_form[username]"))
				.sendKeys(username);
		driver.findElement(By.name("fos_user_registration_form[email]"))
				.sendKeys(email);
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][first]"))
				.sendKeys("pwd1");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.sendKeys("pwd1");
		driver.findElement(
				By.name("fos_user_registration_form[plainPassword][second]"))
				.submit();

		assertTitle("Registration email sent");

		// check sent email
		openProfiler();
		profilerOpenLastPost();
		List<Mail> mails = getMails();
		mails.get(0).assertTo(email);
		mails.get(0)
				.assertMessageContains(
						"Dear Test Person,\n"
								+ "\n"
								+ "You have been registered as a new user in the Tournia.net system.\n"
								+ "With this you can see and change your registration information.\n"
								+ "Updates to you registration information will be mailed to you and your teamcaptain automatically.");
		String activationUrl = mails.get(0).getUrl();

		// opening the activation page
		driver.get(activationUrl);
		assertTitle("Account activated");
		// check that user is logged in
		assertContains(By.cssSelector(".nav .dropdown .dropdown-toggle"),
				"Test Person");

		// invalid url -> show error
		driver.get(activationUrl);
		assertContains(By.cssSelector("#topBlock .alert-danger"),
				"This token is invalid, maybe you've already activated your account?");
	}
}