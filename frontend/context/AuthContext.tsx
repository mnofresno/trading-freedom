import React, { createContext, useContext, useState, useEffect } from 'react';
import storage from '../utils/storage';
import api from '../services/api';
import { useRouter, useSegments } from 'expo-router';

interface AuthContextType {
    signIn: (token: string, userId: string) => Promise<void>;
    signOut: () => Promise<void>;
    userToken: string | null;
    userId: string | null;
    isLoading: boolean;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function useAuth() {
    return useContext(AuthContext);
}

export function AuthProvider({ children }: { children: React.ReactNode }) {
    const [userToken, setUserToken] = useState<string | null>(null);
    const [userId, setUserId] = useState<string | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const rootSegment = useSegments()[0];
    const router = useRouter();

    useEffect(() => {
        const loadToken = async () => {
            try {
                const token = await storage.getItemAsync('user_token');
                const id = await storage.getItemAsync('user_id');
                if (token && id) {
                    setUserToken(token);
                    setUserId(id);
                }
            } catch (e) {
                console.error('Failed to load token', e);
            } finally {
                setIsLoading(false);
            }
        };

        loadToken();
    }, []);

    useEffect(() => {
        if (isLoading) return;

        if (!userToken && rootSegment !== 'login' && rootSegment !== 'signup') {
            router.replace('/login');
        } else if (userToken && (rootSegment === 'login' || rootSegment === 'signup')) {
            router.replace('/(tabs)');
        }
    }, [userToken, rootSegment, isLoading]);

    const signIn = async (token: string, id: string | number) => {
        const idStr = String(id);
        await storage.setItemAsync('user_token', token);
        await storage.setItemAsync('user_id', idStr);
        setUserToken(token);
        setUserId(idStr);
    };

    const signOut = async () => {
        await storage.deleteItemAsync('user_token');
        await storage.deleteItemAsync('user_id');
        setUserToken(null);
        setUserId(null);
    };

    return (
        <AuthContext.Provider value={{ signIn, signOut, userToken, userId, isLoading }}>
            {children}
        </AuthContext.Provider>
    );
}
