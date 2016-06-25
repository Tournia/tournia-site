package com.isbtplanner.site;

import org.testng.annotations.BeforeClass;

import com.isbtplanner.AbstractTest;
import com.isbtplanner.Main;

public abstract class BaseTest extends AbstractTest {

	@BeforeClass
	protected void setupTest() {
		super.setupTest();
		this.baseUrl += Main.TOURNAMENT_URL + "/";
	}

}
