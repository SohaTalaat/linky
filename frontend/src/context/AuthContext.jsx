import { createContext, useContext, useEffect, useState } from "react";
import { getMe, loginUser, registerUser, logoutUser } from "../api/auth";

const AuthContext = createContext();

export function Authprovider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const loadUser = async () => {
    const token = localStorage.getItem("token");

    if (!token) {
      setLoading(false);
      return;
    }

    try {
      const response = await getMe();
      setUser(response.data.user);
    } catch {
      localStorage.removeItem("token");
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadUser();
  }, []);

  const login = async (FormData) => {
    const response = await loginUser(FormData);
    localStorage.setItem("token", response.data.token);
    setUser(response.data.user);
  };

  const register = async (FormData) => {
    const response = await registerUser(FormData);
    localStorage.setItem("token", response.data.token);
    setUser(response.data.user);
  };

  const logout = async () => {
    try {
      await logoutUser();
    } catch {
      //
    } finally {
      localStorage.removeItem("token");
      setUser(null);
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        loading,
        login,
        register,
        logout,
        isAuthenticated: !!user,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
