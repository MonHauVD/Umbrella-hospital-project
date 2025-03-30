package com.example.do_an_tot_nghiep.UnitTest;

import static com.example.do_an_tot_nghiep.Helper.Tooltip.beautifierDatetime;
import static com.example.do_an_tot_nghiep.Helper.Tooltip.getDateDifference;
import static com.example.do_an_tot_nghiep.Helper.Tooltip.getToday;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertThrows;
import static org.mockito.Mockito.when;

import android.content.Context;

import com.example.do_an_tot_nghiep.R;

import org.junit.Before;
import org.junit.Test;
import org.mockito.Mock;
import org.mockito.MockitoAnnotations;

import java.time.format.DateTimeParseException;
import java.util.Date;
import java.util.concurrent.TimeUnit;

public class TooltipTest {

    @Mock
    Context context;

    @Before
    public void setup(){
        MockitoAnnotations.initMocks(this); // Initialize mocks

        when(context.getString(R.string.at))
                .thenReturn("at");
    }

    @Test
    public void testValidDateTime() {
        String input = "2025-03-27 14:30:45";
        String expected = "Thu, 27-03-2025 " + context.getString(R.string.at) + " 14:30";
        assertEquals(expected, beautifierDatetime(context, input));
    }

    @Test
    public void testNormalDate() {
        String input = "2025-03-27 14:30:45";
        String expected = "Thu, 27-03-2025 at 14:30";
        assertEquals(expected, beautifierDatetime(context, input));
    }

    @Test
    public void testShortDate() {
        String input = "1-1-25 5:5:5";
        String expected = "Fri, 01-01-2025 at 05:05";
        assertEquals(expected, beautifierDatetime(context, input));
    }

    @Test
    public void testLongDate() {
        String input = "123-456-78901 25:61:61";
        assertThrows(DateTimeParseException.class, () -> beautifierDatetime(context, input));
    }

    @Test
    public void testInvalidOrderDate() {
        String input = "2025-03-27 14:30:45";
        assertThrows(DateTimeParseException.class, () -> beautifierDatetime(context, input));
    }

    @Test
    public void testMinDate() {
        String input = "01-01-0001 00:00:00";
        String expected = "Mon, 01-01-0001 at 00:00";
        assertEquals(expected, beautifierDatetime(context, input));
    }

    @Test
    public void testMaxDate() {
        String input = "31-12-9999 23:59:59";
        String expected = "Fri, 31-12-9999 at 23:59";
        assertEquals(expected, beautifierDatetime(context, input));
    }

    @Test
    public void testDayExceeding31() {
        String input = "32-03-2025 14:30:45";
        assertThrows(DateTimeParseException.class, () -> beautifierDatetime(context, input));
    }

    @Test
    public void testDayExceedingMonthLimit() {
        String input = "30-02-2025 14:30:45";
        assertThrows(DateTimeParseException.class, () -> beautifierDatetime(context, input));
    }

    @Test
    public void testYearExceeding9999() {
        String input = "27-03-9999 14:30:45";
        System.out.println(beautifierDatetime(context, input));
        assertThrows(DateTimeParseException.class, () -> beautifierDatetime(context, input));
    }

    @Test
    public void testYear0001() {
        String input = "27-03-0001 14:30:45";
        String expected = "Thu, 27-03-0001 at 14:30";
        assertEquals(expected, beautifierDatetime(context, input));
    }

    @Test
    public void testDayWithoutLeadingZero() {
        String input = "5-03-2025 14:30:45";
        String expected = "Wed, 05-03-2025 at 14:30";
        assertEquals(expected, beautifierDatetime(context, input));
    }

    @Test
    public void testInvalidString() {
        String input = "Hello World";
        assertThrows(DateTimeParseException.class, () -> beautifierDatetime(context, input));
    }

    @Test
    public void testMonthWithoutLeadingZero() {
        String input = "27-3-2025 14:30:45";
        String expected = "Thu, 27-03-2025 at 14:30";
        assertEquals(expected, beautifierDatetime(context, input));
    }

    @Test
    public void testDateWithBackslashSeparator() {
        String input = "27\03\2025 14:30:45";
        assertThrows(DateTimeParseException.class, () -> beautifierDatetime(context, input));
    }



    @Test
    public void getTodayTest(){
        String expected = "2025-03-28";
        assertEquals(expected, getToday());
    }

    @Test
    public void getDateDifferenceTest(){
        Date input = new Date(2025, 03, 27);
        Date input1  = new Date(2025, 03, 24);
        int expected = 72;
        assertEquals(expected, getDateDifference(input1, input, TimeUnit.HOURS));
    }
}
