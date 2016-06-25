package com.isbtplanner;

import java.util.concurrent.TimeUnit;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.firefox.FirefoxDriver;

public class Main {

	public static final String BASEURL = "http://localhost/isbt-repo/public_html/app_dev.php/";

	private static WebDriver driver;

	public static final String USER_NORMAL = "selenium_normal";
	public static final String USER_ADMIN = "selenium_admin";

	public final static String TOURNAMENT_URL = "london2013";

	/**
	 * Singleton method for getting the WebDriver object
	 * 
	 * @return
	 */
	protected static WebDriver getDriver() {
		if (driver == null) {
			// Create a new instance of the Firefox driver
			driver = new FirefoxDriver();

			// set implicit wait to 10 seconds for javascript elements to load
			driver.manage().timeouts().implicitlyWait(10, TimeUnit.SECONDS);
		}

		return driver;
	}

}