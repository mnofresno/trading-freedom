import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

// Polyfill for web platform using localStorage
const webStorage = {
    async getItemAsync(key: string): Promise<string | null> {
        if (Platform.OS === 'web') {
            try {
                return localStorage.getItem(key);
            } catch (e) {
                console.error('Error reading from localStorage', e);
                return null;
            }
        }
        return SecureStore.getItemAsync(key);
    },
    
    async setItemAsync(key: string, value: string): Promise<void> {
        if (Platform.OS === 'web') {
            try {
                localStorage.setItem(key, value);
            } catch (e) {
                console.error('Error writing to localStorage', e);
            }
            return;
        }
        return SecureStore.setItemAsync(key, value);
    },
    
    async deleteItemAsync(key: string): Promise<void> {
        if (Platform.OS === 'web') {
            try {
                localStorage.removeItem(key);
            } catch (e) {
                console.error('Error removing from localStorage', e);
            }
            return;
        }
        return SecureStore.deleteItemAsync(key);
    },
};

export default webStorage;

