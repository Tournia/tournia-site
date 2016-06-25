package com.isbtplanner.front;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.testng.annotations.Test;

public class BrowseTest extends BaseTest {

	@Test(groups = { "browse" })
	public void topTest() {
		this.goUrl("");
		assertUrl("");
		assertTitle("Index");

		WebElement tournamentsLink = driver.findElement(By
				.partialLinkText("TOURNAMENTS"));
		tournamentsLink.click();
		assertUrl("tournaments.html");
		assertTitle("Tournaments");

		WebElement betaLink = driver.findElement(By.linkText("JOIN THE BETA"));
		betaLink.click();
		assertUrl("index.html");
		assertTitle("Index");

		WebElement loginLink = driver.findElement(By.id("topBlock"))
				.findElement(By.linkText("LOG IN"));
		loginLink.click();
		assertUrl("login.html");
		assertTitle("Log in");

		WebElement resetPasswordLink = driver.findElement(
				By.cssSelector("#sign_in1 .remember")).findElement(
				By.linkText("Forgot password?"));
		resetPasswordLink.click();
		assertUrl("reset.html");
		assertTitle("Reset password");
	}

	@Test(groups = { "todo" }, enabled = false)
	public void feedbackTest() {
		this.goUrl("");

		// feedback dialog
		WebElement footerFeedbackLink = driver
				.findElement(By.tagName("footer")).findElement(
						By.className("btn-warning"));
		footerFeedbackLink.click();
		assertExists(By.cssSelector(".submit_row .uvStyle-button"));
		WebElement closeDialogLink = driver.findElement(
				By.id("uvw-dialog-close-uv-3")).findElement(
				By.tagName("button"));
		closeDialogLink.click();
	}

	@Test(groups = { "browse" })
	/**
	 * Test the menu options before and after logging in
	 */
	public void loginTest() {
		this.goUrl("");

		// before login only login link
		WebElement loginLink = driver.findElement(By.id("topBlock"))
				.findElement(By.linkText("LOG IN"));
		loginLink.click();
		assertUrl("login.html");
		assertTitle("Log in");

		// after login more options
		login("normal");

		driver.findElement(By.id("topBlock"))
				.findElement(By.linkText("Selenium normal")).click();
		WebElement editLink = driver.findElement(By.id("topBlock"))
				.findElement(By.cssSelector(".dropdown .dropdown-menu"))
				.findElement(By.linkText("Edit account"));
		editLink.click();
		assertUrl("profile/edit");

		driver.findElement(By.id("topBlock"))
				.findElement(By.linkText("Selenium normal")).click();
		WebElement passwordLink = driver.findElement(By.id("topBlock"))
				.findElement(By.linkText("Change password"));
		passwordLink.click();
		assertUrl("profile/change-password");

		driver.findElement(By.id("topBlock"))
				.findElement(By.linkText("Selenium normal")).click();
		WebElement logoutLink = driver.findElement(By.id("topBlock"))
				.findElement(By.linkText("Logout"));
		logoutLink.click();
		assertUrl("");
		assertTitle("Index");
	}
}