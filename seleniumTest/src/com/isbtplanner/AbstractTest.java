package com.isbtplanner;

import static org.testng.AssertJUnit.assertEquals;
import static org.testng.AssertJUnit.assertTrue;
import static org.testng.AssertJUnit.fail;

import java.util.ArrayList;
import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.Keys;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.testng.annotations.AfterClass;
import org.testng.annotations.BeforeClass;

public abstract class AbstractTest {

	protected String baseUrl;

	protected WebDriver driver;

	protected String mainWindowHandle;

	@BeforeClass
	protected void setupTest() {
		this.baseUrl = Main.BASEURL;
		this.driver = Main.getDriver();
		this.mainWindowHandle = driver.getWindowHandle();
	}

	@AfterClass
	protected void teardownTest() {
		closeWindowsExceptMain();
		// this.driver.quit();
	}

	/**
	 * Visit a URL with the WebDriver
	 * 
	 * @param suffixUrl
	 *            The last part of the url. Visited url will be: this.baseUrl +
	 *            suffixUrl
	 */
	protected void goUrl(String suffixUrl) {
		driver.get(this.baseUrl + suffixUrl);
	}

	/**
	 * Login a user
	 * 
	 * @param level
	 *            Can be "normal", "admin"
	 */
	protected void login(String level) {
		String username = "";
		if (level.equals("normal")) {
			username = Main.USER_NORMAL;
		} else if (level.equals("admin")) {
			username = Main.USER_ADMIN;
		}

		goUrl("login.html");
		driver.findElement(By.name("_username")).clear();
		driver.findElement(By.name("_username")).sendKeys(username);
		driver.findElement(By.name("_password")).sendKeys(username);
		driver.findElement(By.name("_password")).submit();

		assertContains(By.cssSelector(".nav .dropdown-toggle"), "Selenium "
				+ level);
	}

	/**
	 * Assert the page title
	 * 
	 * @param expected
	 *            The expected page title
	 */
	public void assertTitle(String expected) {
		assertEquals(expected, driver.getTitle());
	}

	/**
	 * Assert the url
	 * 
	 * @param expected
	 *            The expected url suffix, i.e. the url without the baseUrl
	 */
	public void assertUrl(String expected) {
		String suffixUrl = driver.getCurrentUrl().replace(this.baseUrl, "");
		assertEquals(expected, suffixUrl);
	}

	/**
	 * Assert that an element with an Id exists in driver
	 * 
	 * @param id
	 *            ID of element to look for
	 * @result Same as this.assertExists(By.Id(element));
	 */
	public void assertIdExists(String id) {
		this.assertExists(By.id(id));
	}

	/**
	 * Assert that an element exists in driver
	 * 
	 * @param byElement
	 *            for example By.id("abc") or By.cssSelector(".class #id")
	 */
	public void assertExists(By byElement) {
		try {
			driver.findElement(byElement);
		} catch (NoSuchElementException e) {
			// element not found
			fail("Element " + byElement + " not found");
		}
	}

	/**
	 * Check whether the current page is an error page
	 * 
	 * @param expected
	 *            Whether the current page is an error page (true) or not
	 *            (false)
	 */
	public void assertIsError(boolean expected) {
		boolean isError = true;

		try {
			driver.findElement(By.className("text-exception"));
		} catch (NoSuchElementException e) {
			// error block not found
			isError = false;
		}

		assertEquals(expected, isError);
	}

	/**
	 * Assert that a specific text is in a element
	 * 
	 * @param byElement
	 *            The element that should contain the text. For example
	 *            By.id("abc")
	 * @param text
	 *            The text to look for in the element
	 */
	public void assertContains(By byElement, String text) {
		assertTrue(driver.findElement(byElement).getText().contains(text));
	}

	/**
	 * Assert that a form value matches the expected string
	 * 
	 * @param byElement
	 *            The form element that should have the value. For example
	 *            By.id("abc")
	 * @param expected
	 *            The value to look for in the form element
	 */
	public void assertValue(By byElement, String expected) {
		assertTrue(driver.findElement(byElement).getAttribute("value")
				.equals(expected));
	}

	private void closeWindowsExceptMain() {
		// close other windows except the main window
		for (String handle : driver.getWindowHandles()) {
			if (!handle.equals(mainWindowHandle)) {
				driver.switchTo().window(handle);
				driver.close();
			}
		}
		driver.switchTo().window(mainWindowHandle);
	}

	/**
	 * Open the Symfony profiler in a new window
	 */
	public void openProfiler() {
		closeWindowsExceptMain();

		// find link to profiler
		WebElement profilerBar = driver.findElement(By
				.className("sf-toolbarreset"));
		List<WebElement> allBlocks = profilerBar.findElements(By
				.className("sf-toolbar-block"));
		WebElement profilerLink = allBlocks.get(2).findElement(By.tagName("a"));

		// open new window
		String selectAll = Keys.chord(Keys.SHIFT, Keys.RETURN);
		profilerLink.sendKeys(selectAll);

		// switch driver to profiler window
		for (String handle : driver.getWindowHandles()) {
			if (!handle.equals(mainWindowHandle)) {
				driver.switchTo().window(handle);
			}
		}

	}

	/**
	 * Open on the profiler page the last post request Assumes the profiler
	 * window is the active window
	 */
	public void profilerOpenLastPost() {
		// open page with last 10 requests
		WebElement last10Link = driver.findElement(By.id("resume-view-all"));
		last10Link.click();

		// search for last POST method
		List<WebElement> allPostRows = driver
				.findElement(By.id("collector-content"))
				.findElement(By.tagName("tbody"))
				.findElements(By.tagName("tr"));
		for (int i = 0; i < allPostRows.size(); i++) {
			List<WebElement> rowCells = allPostRows.get(i).findElements(
					By.tagName("td"));
			if (rowCells.get(2).getText().equals("POST")) {
				// row is POST request
				rowCells.get(0).findElement(By.tagName("a")).click();
				break;
			}
		}
	}

	/**
	 * Get List with Mails that are sent, according to the opened profiler page
	 * 
	 * @return
	 */
	public List<Mail> getMails() {
		// open email in profiler
		WebElement emailsLink = driver.findElement(
				By.cssSelector("#navigation li.swiftmailer")).findElement(
				By.tagName("a"));
		emailsLink.click();

		// result list
		List<Mail> res = new ArrayList<Mail>();

		// go through all mails
		List<WebElement> allMailElements = driver.findElements(By
				.cssSelector("#collector-content .alt .even"));
		for (WebElement mailElement : allMailElements) {
			// create Mail object and add it to result list
			Mail mail = new Mail(mailElement);
			res.add(mail);
		}

		return res;
	}

	/**
	 * Return a random string
	 * 
	 * @return
	 */
	public String rand() {
		return System.nanoTime() + "";
	}
}
