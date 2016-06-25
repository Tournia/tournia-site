package com.isbtplanner.site;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.testng.annotations.Test;

public class BrowseTest extends BaseTest {

	@Test(groups = { "browse" })
	public void browseTest() {
		this.goUrl("");
		assertUrl("index.html");
		assertTitle("General");

		// general
		driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("About")).click();
		WebElement generalLink = driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("General"));
		generalLink.click();
		assertUrl("index.html");
		assertTitle("General");

		// players
		WebElement playersLink = driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("Players"));
		playersLink.click();
		assertUrl("players.html");
		assertTitle("Players");

		// groups
		WebElement groupsLink = driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("Groups"));
		groupsLink.click();
		assertUrl("groups.html");
		assertTitle("Groups");

		// payment info
		driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("Payment")).click();
		WebElement paymentInfoLink = driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("Payment info"));
		paymentInfoLink.click();
		assertUrl("payment/info.html");
		assertTitle("Payment information");

		// paypal
		driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("Payment")).click();
		WebElement paypalInfoLink = driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("PayPal payment"));
		paypalInfoLink.click();
		assertTitle("Log in");

		goUrl("");
		// Live
		driver.findElement(By.id("navbarSite"))
				.findElement(By.linkText("Live")).click();
		assertUrl("live.html");
		assertTitle("Live password");

		// registration
		driver.findElement(By.className("head"))
				.findElement(By.linkText("Registration")).click();
		assertTitle("Log in");
	}
}