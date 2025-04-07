package com.example.do_an_tot_nghiep.UnitTest.LoginTest;

import static org.junit.Assert.*;
import static org.mockito.Mockito.*;

import androidx.arch.core.executor.testing.InstantTaskExecutorRule;
import androidx.lifecycle.MutableLiveData;
import androidx.lifecycle.Observer;

import com.example.do_an_tot_nghiep.Container.Login;
import com.example.do_an_tot_nghiep.Loginpage.LoginViewModel;
import com.example.do_an_tot_nghiep.Configuration.HTTPRequest;

import org.junit.Before;
import org.junit.Rule;
import org.junit.Test;

import java.lang.reflect.Field;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginViewModelTest {
    @Rule
    public InstantTaskExecutorRule instantTaskExecutorRule = new InstantTaskExecutorRule();

    private LoginViewModel viewModel;
    private HTTPRequest mockApi;
    private Call<Login> mockCall;

    @Before
    public void setup() throws NoSuchFieldException, IllegalAccessException {
        viewModel = new LoginViewModel();

        Field animationField = LoginViewModel.class.getDeclaredField("animation");
        animationField.setAccessible(true);
        animationField.set(viewModel, new MutableLiveData<>());

        mockApi = mock(HTTPRequest.class);
        mockCall = mock(Call.class);
    }

    @Test
    public void testLoginWithPhone_Success() {

        // Arrange
        Login mockLoginResponse = new Login();
        mockLoginResponse.setResult(0);
        mockLoginResponse.setMsg("Success");

        final boolean[] called = {false};

        // Call retrofit callback manually
        doAnswer(invocation -> {
            Callback<Login> callback = invocation.getArgument(0);
            callback.onResponse(mockCall, Response.success(mockLoginResponse));
            return null;
        }).when(mockCall).enqueue(any());

        when(mockApi.login(eq("0865957312"), eq("12345"), eq("patient")))
                .thenReturn(mockCall);

        // Act
        viewModel.loginWithPhone("0865957312", "123456");

        // Assert
        viewModel.getLoginWithPhoneResponse().observeForever(login -> {
            assertNotNull(login);
            int x = login.getResult();
            assertEquals(1, x);
            called[0] = true;
        });
        assertTrue("Observer was not called!", called[0]);
    }

    @Test
    public void testLoginWithPhone_Failure() {
        final boolean[] called = {false};
        // Arrange: simulate unsuccessful response
        doAnswer(invocation -> {
            Callback<Login> callback = invocation.getArgument(0);
            callback.onResponse(mockCall, Response.error(400, okhttp3.ResponseBody.create(null, "Bad request")));
            return null;
        }).when(mockCall).enqueue(any());

        when(mockApi.login("0123456789", "wrongPass", "patient")).thenReturn(mockCall);

        // Act
        viewModel.loginWithPhone("0123456789", "wrongPass");

        // Assert
        viewModel.getLoginWithPhoneResponse().observeForever(login -> {
            assertNull(login);
            called[0] = true;
        });

        assertTrue("Observer was not called!", called[0]);
    }

    @Test
    public void testLoginWithPhone_NetworkError() {
        final boolean[] called = {false};
        // Arrange
        doAnswer(invocation -> {
            Callback<Login> callback = invocation.getArgument(0);
            callback.onFailure(mockCall, new Throwable("Network error"));
            return null;
        }).when(mockCall).enqueue(any());

        when(mockApi.login("0123456789", "any", "patient")).thenReturn(mockCall);

        // Act
        viewModel.loginWithPhone("0123456789", "any");

        // Assert
        viewModel.getLoginWithPhoneResponse().observeForever(login -> {
            called[0] = true;
            assertNull(login);
        });
        assertTrue("Observer was not called!", called[0]);
    }

    @Test
    public void testAnimationLiveData() {
        // Arrange
        Observer<Boolean> observer = mock(Observer.class);
        viewModel.getAnimation().observeForever(observer);

        // Act
        viewModel.getAnimation().setValue(true);
        viewModel.getAnimation().setValue(false);

        // Assert
        verify(observer).onChanged(true);
        verify(observer).onChanged(false);
    }

    @Test
    public void testLoginWithGoogle_Success() {
        // Arrange
        Login mockLoginResponse = new Login();
        mockLoginResponse.setResult(1);
        mockLoginResponse.setMsg("Google login success");

        final boolean[] called = {false};

        // Giả lập retrofit callback trả về thành công
        doAnswer(invocation -> {
            Callback<Login> callback = invocation.getArgument(0);
            callback.onResponse(mockCall, Response.success(mockLoginResponse));
            return null;
        }).when(mockCall).enqueue(any());

        when(mockApi.loginWithGoogle(eq("user@example.com"), eq("correctPassword"), eq("patient")))
                .thenReturn(mockCall);

        // Act
        viewModel.loginWithGoogle("user@example.com", "correctPassword");

        // Assert
        viewModel.getLoginWithGoogleResponse().observeForever(login -> {
            assertNotNull(login);
            int x  =  login.getResult();
            assertEquals(1,x);
            assertEquals("Google login success", login.getMsg());
            called[0] = true;
        });

        assertTrue("Observer was not called!", called[0]);
    }

    @Test
    public void testLoginWithGoogle_Failure() {
        // Arrange
        final boolean[] called = {false};

        // Giả lập retrofit callback thất bại (sai mật khẩu chẳng hạn)
        doAnswer(invocation -> {
            Callback<Login> callback = invocation.getArgument(0);
            callback.onResponse(mockCall, Response.error(401, okhttp3.ResponseBody.create(null, "Unauthorized")));
            return null;
        }).when(mockCall).enqueue(any());

        when(mockApi.loginWithGoogle(eq("user@example.com"), eq("wrongPassword"), eq("patient")))
                .thenReturn(mockCall);

        // Act
        viewModel.loginWithGoogle("user@example.com", "wrongPassword");

        // Assert
        viewModel.getLoginWithGoogleResponse().observeForever(login -> {
            assertNull("Login should be null on failure", login);
            called[0] = true;
        });

        assertTrue("Observer was not called!", called[0]);
    }


}
