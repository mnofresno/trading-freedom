import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, Alert, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Link } from 'expo-router';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';

export default function LoginScreen() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [loading, setLoading] = useState(false);
    const auth = useAuth();

    const handleLogin = async () => {
        if (!email || !password) {
            Alert.alert('Error', 'Please enter email and password');
            return;
        }

        setLoading(true);
        try {
            const response = await api.post('/auth/login', { email, password });
            const { token, user_id } = response.data;
            await auth?.signIn(token, user_id || response.data.userId);
        } catch (error: any) {
            console.log(error);
            const message = error.response?.data?.message || error.message || 'Connection failed';
            Alert.alert('Login Failed', message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <View className="flex-1 justify-center items-center bg-gray-100 dark:bg-gray-900 p-6">
            <View className="w-full max-w-sm bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg">
                <Text className="text-3xl font-bold text-center text-gray-900 dark:text-white mb-8">
                    Welcome Back
                </Text>

                <View className="space-y-4">
                    <View>
                        <Text className="text-gray-700 dark:text-gray-300 mb-1">Email</Text>
                        <TextInput
                            className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="you@example.com"
                            placeholderTextColor="#9CA3AF"
                            keyboardType="email-address"
                            autoCapitalize="none"
                            value={email}
                            onChangeText={setEmail}
                        />
                    </View>

                    <View>
                        <Text className="text-gray-700 dark:text-gray-300 mb-1">Password</Text>
                        <View className="relative justify-center">
                            <TextInput
                                className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 pr-12"
                                placeholder="••••••••"
                                placeholderTextColor="#9CA3AF"
                                secureTextEntry={!showPassword}
                                value={password}
                                onChangeText={setPassword}
                            />
                            <TouchableOpacity
                                onPress={() => setShowPassword(!showPassword)}
                                className="absolute right-4"
                            >
                                <Ionicons name={showPassword ? "eye-off" : "eye"} size={24} color="#9CA3AF" />
                            </TouchableOpacity>
                        </View>
                    </View>

                    <TouchableOpacity
                        className="w-full bg-indigo-600 py-3 rounded-lg flex-row justify-center items-center mt-6"
                        onPress={handleLogin}
                        disabled={loading}
                    >
                        {loading ? (
                            <ActivityIndicator color="white" />
                        ) : (
                            <Text className="text-white font-semibold text-lg">Sign In</Text>
                        )}
                    </TouchableOpacity>
                </View>

                <View className="mt-6 flex-row justify-center">
                    <Text className="text-gray-600 dark:text-gray-400">Don't have an account? </Text>
                    <Link href="/signup" asChild>
                        <TouchableOpacity>
                            <Text className="text-indigo-600 font-semibold">Sign Up</Text>
                        </TouchableOpacity>
                    </Link>
                </View>
            </View>
        </View>
    );
}
