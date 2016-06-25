package com.isbtplanner.front;

import org.testng.annotations.BeforeClass;

import com.isbtplanner.AbstractTest;

public abstract class BaseTest extends AbstractTest {

	@BeforeClass
	protected void setupTest() {
		super.setupTest();
	}

}
