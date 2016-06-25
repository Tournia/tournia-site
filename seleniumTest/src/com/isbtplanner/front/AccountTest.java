package com.isbtplanner.front;

import java.util.List;

import org.openqa.selenium.By;
import org.testng.annotations.Test;

import com.isbtplanner.Mail;
import com.isbtplanner.Main;

public class AccountTest extends BaseTest {

	@Test
	/**
	 * Test logging in
	 */
	public void loginTest() {
		goUrl("login.html");

		// empty form
		driver.findElement(By.name("_username")).sendKeys("");
		driver.findElement(By.name("_password")).sendKeys("");
		driver.findElement(By.name("_password")).submit();
		assertTitle("Log in");
		assertContains(By.className("alert-danger"),
				"Incorrect username or password");

		// wrong values
		driver.findElement(By.name("_username")).sendKeys("abc");
		driver.findElement(By.name("_password")).sendKeys("def");
		driver.findElement(By.name("_password")).submit();
		assertTitle("Log in");
		assertContains(By.className("alert-danger"),
				"Incorrect username or password");

		// correct values
		driver.findElement(By.name("_username")).clear();
		driver.findElement(By.name("_username")).sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("_password")).sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("_password")).submit();
		assertContains(By.cssSelector(".nav .dropdown-toggle"),
				"Selenium normal");
	}

	@Test
	/**
	 * Test editing the account with incorrect values
	 */
	public void editIncorrectTest() {
		login("normal");

		goUrl("profile/edit");

		// leave password empty
		driver.findElement(By.name("fos_user_profile_form[name]"))
				.sendKeys("1");
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.submit();
		assertContains(By.cssSelector(".container form"),
				"This value should be the user current password");

		// leave fields empty
		driver.findElement(By.name("fos_user_profile_form[name]")).clear();
		driver.findElement(By.name("fos_user_profile_form[username]")).clear();
		driver.findElement(By.name("fos_user_profile_form[email]")).clear();
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.submit();
		assertContains(By.cssSelector(".container form"),
				"Please enter your name");
		assertContains(By.cssSelector(".container form"),
				"Please enter a username");
		assertContains(By.cssSelector(".container form"),
				"Please enter an email");

		// incorrect fields
		driver.findElement(By.name("fos_user_profile_form[username]")).clear();
		driver.findElement(By.name("fos_user_profile_form[username]"))
				.sendKeys(Main.USER_ADMIN);
		driver.findElement(By.name("fos_user_profile_form[email]")).clear();
		driver.findElement(By.name("fos_user_profile_form[email]")).sendKeys(
				Main.USER_ADMIN + "@tournia.net");
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.submit();
		assertContains(By.cssSelector(".container form"),
				"The username is already used");
		assertContains(By.cssSelector(".container form"),
				"The email is already used");
	}

	@Test
	/**
	 * Test editing the account with correct values
	 */
	public void editCorrectTest() {
		login("normal");
		goUrl("profile/edit");

		// fill in correct fields
		driver.findElement(By.name("fos_user_profile_form[name]")).clear();
		driver.findElement(By.name("fos_user_profile_form[name]")).sendKeys(
				"abc");
		driver.findElement(By.name("fos_user_profile_form[username]")).clear();
		driver.findElement(By.name("fos_user_profile_form[username]"))
				.sendKeys(Main.USER_NORMAL + "def");
		driver.findElement(By.name("fos_user_profile_form[email]")).clear();
		driver.findElement(By.name("fos_user_profile_form[email]")).sendKeys(
				Main.USER_NORMAL + "ghi@tournia.net");
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.submit();
		assertContains(By.className("alert-success"),
				"The profile has been updated");

		// check changed username
		goUrl("logout.html");
		goUrl("login.html");
		driver.findElement(By.name("_username")).sendKeys(
				Main.USER_NORMAL + "def");
		driver.findElement(By.name("_password")).sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("_password")).submit();

		// check other changed values
		goUrl("profile/edit");
		assertContains(By.cssSelector(".nav .dropdown-toggle"), "abc");
		assertValue(By.name("fos_user_profile_form[name]"), "abc");
		assertValue(By.name("fos_user_profile_form[username]"),
				"selenium_normaldef");
		assertValue(By.name("fos_user_profile_form[email]"),
				"selenium_normalghi@tournia.net");

		// change back to original values
		driver.findElement(By.name("fos_user_profile_form[name]")).clear();
		driver.findElement(By.name("fos_user_profile_form[name]")).sendKeys(
				"Selenium normal");
		driver.findElement(By.name("fos_user_profile_form[username]")).clear();
		driver.findElement(By.name("fos_user_profile_form[username]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("fos_user_profile_form[email]")).clear();
		driver.findElement(By.name("fos_user_profile_form[email]")).sendKeys(
				Main.USER_NORMAL + "@tournia.net");
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("fos_user_profile_form[current_password]"))
				.submit();
		assertContains(By.className("alert-success"),
				"The profile has been updated");
	}

	@Test
	/**
	 * Test changing the password with incorrect form values
	 */
	public void passwordIncorrectTest() {
		login("normal");
		goUrl("profile/change-password");

		// leave current password empty
		driver.findElement(
				By.name("fos_user_change_password_form[current_password]"))
				.clear();
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][first]"))
				.sendKeys("abc");
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.sendKeys("abc");
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.submit();
		assertContains(By.cssSelector(".container form"),
				"This value should be the user current password");

		// leave new password empty
		driver.findElement(
				By.name("fos_user_change_password_form[current_password]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][first]"))
				.clear();
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.clear();
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.submit();
		assertContains(By.cssSelector(".container form"),
				"Please enter a password");

		// different new passwords
		driver.findElement(
				By.name("fos_user_change_password_form[current_password]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][first]"))
				.sendKeys("a");
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.sendKeys("b");
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.submit();
		assertContains(By.cssSelector(".container form"),
				"The entered passwords don't match");

	}

	@Test
	/**
	 * Test changing the password with correct form values
	 */
	public void passwordCorrectTest() {
		login("normal");
		goUrl("profile/change-password");

		// fill in correct fields
		driver.findElement(
				By.name("fos_user_change_password_form[current_password]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][first]"))
				.sendKeys("abc");
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.sendKeys("abc");
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.submit();
		assertContains(By.className("alert-success"),
				"The password has been changed");

		checkChangedPassword();
	}

	private void checkChangedPassword() {
		// check changed password
		goUrl("logout.html");
		goUrl("login.html");
		driver.findElement(By.name("_username")).sendKeys(Main.USER_NORMAL);
		driver.findElement(By.name("_password")).sendKeys("abc");
		driver.findElement(By.name("_password")).submit();

		// change back to original values
		goUrl("profile/change-password");
		driver.findElement(
				By.name("fos_user_change_password_form[current_password]"))
				.sendKeys("abc");
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][first]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.sendKeys(Main.USER_NORMAL);
		driver.findElement(
				By.name("fos_user_change_password_form[plainPassword][second]"))
				.submit();
		assertContains(By.className("alert-success"),
				"The password has been changed");
	}

	@Test
	/**
	 * Test the logout functionality
	 */
	public void logoutTest() {
		goUrl("logout.html"); // logout because previous method logged in
		assertContains(By.id("topBlock"), "LOG IN");

		// logging out without being logged in
		goUrl("logout.html");
		assertContains(By.id("topBlock"), "LOG IN");

		login("normal");

		assertContains(By.id("topBlock"), "Selenium normal");

		// now normally logging out
		goUrl("logout.html");
		assertContains(By.id("topBlock"), "LOG IN");
		assertUrl("");
	}

	@Test
	/**
	 * Test resetting the password
	 */
	public void resetPasswordTest() {
		goUrl("reset.html");

		// leave email empty
		driver.findElement(By.id("resetEmail")).clear();
		driver.findElement(By.id("resetEmail")).submit();
		assertContains(By.cssSelector("#reset_pwd form"),
				"The email address \"\" does not exist");

		// enter incorrect email
		driver.findElement(By.id("resetEmail")).sendKeys("bogus");
		driver.findElement(By.id("resetEmail")).submit();

		// enter unknown email
		driver.findElement(By.id("resetEmail")).sendKeys(
				"bogus@tournia.net");
		driver.findElement(By.id("resetEmail")).submit();

		// enter correct email
		driver.findElement(By.id("resetEmail")).sendKeys(
				Main.USER_NORMAL + "@tournia.net");
		driver.findElement(By.id("resetEmail")).submit();

		// check sent email
		openProfiler();
		profilerOpenLastPost();
		List<Mail> mails = getMails();
		mails.get(0).assertTo(Main.USER_NORMAL + "@tournia.net");
		mails.get(0).assertMessageContains(
				"Dear Selenium normal,\n" + "\n"
						+ "You have requested to reset your password.\n"
						+ "You can do that by going to this page");
		String resetUrl = mails.get(0).getUrl();

		// opening the reset page
		driver.get(resetUrl);
		driver.findElement(
				By.name("fos_user_resetting_form[plainPassword][first]"))
				.sendKeys("abc");
		driver.findElement(
				By.name("fos_user_resetting_form[plainPassword][second]"))
				.sendKeys("abc");
		driver.findElement(
				By.name("fos_user_resetting_form[plainPassword][first]"))
				.submit();

		// check new password
		checkChangedPassword();
	}

}