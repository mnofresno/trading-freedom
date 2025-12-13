import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, Alert, ActivityIndicator } from 'react-native';
import { Link, useRouter } from 'expo-router';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';

export default function SignupScreen() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [repeatPassword, setRepeatPassword] = useState(''); // Legacy requires this check probably?
    const [loading, setLoading] = useState(false);
    const router = useRouter();
    const auth = useAuth(); // for auto-login

    const handleSignup = async () => {
        if (!name || !email || !password) {
            Alert.alert('Error', 'Please fill in all fields');
            return;
        }
        if (password !== repeatPassword) {
            Alert.alert('Error', 'Passwords do not match');
            return;
        }

        setLoading(true);
        try {
            await api.post('/auth/register', { name, email, password, repeatPassword });
            // Auto login after register? Legacy implementation did this.
            const loginResponse = await api.post('/auth/login', { email, password });
            const { token, user_id } = loginResponse.data;
            await auth?.signIn(token, user_id || loginResponse.data.userId);
        } catch (error: any) {
            console.log(error);
            const message = error.response?.data?.message || error.message || 'Registration failed';
            Alert.alert('Registration Failed', message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <View className="flex-1 justify-center items-center bg-gray-100 dark:bg-gray-900 p-6">
            <View className="w-full max-w-sm bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg">
                <Text className="text-3xl font-bold text-center text-gray-900 dark:text-white mb-8">
                    Create Account
                </Text>

                <View className="space-y-4">
                    <View>
                        <Text className="text-gray-700 dark:text-gray-300 mb-1">Name</Text>
                        <TextInput
                            className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="John Doe"
                            placeholderTextColor="#9CA3AF"
                            value={name}
                            onChangeText={setName}
                        />
                    </View>
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
                        <TextInput
                            className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="••••••••"
                            placeholderTextColor="#9CA3AF"
                            secureTextEntry
                            value={password}
                            onChangeText={setPassword}
                        />
                    </View>
                    <View>
                        <Text className="text-gray-700 dark:text-gray-300 mb-1">Confirm Password</Text>
                        <TextInput
                            className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                            placeholder="••••••••"
                            placeholderTextColor="#9CA3AF"
                            secureTextEntry
                            value={repeatPassword}
                            onChangeText={setRepeatPassword}
                        />
                    </View>

                    <TouchableOpacity
                        className="w-full bg-indigo-600 py-3 rounded-lg flex-row justify-center items-center mt-6"
                        onPress={handleSignup}
                        disabled={loading}
                    >
                        {loading ? (
                            <ActivityIndicator color="white" />
                        ) : (
                            <Text className="text-white font-semibold text-lg">Sign Up</Text>
                        )}
                    </TouchableOpacity>
                </View>

                <View className="mt-6 flex-row justify-center">
                    <Text className="text-gray-600 dark:text-gray-400">Already have an account? </Text>
                    <Link href="/login" asChild>
                        <TouchableOpacity>
                            <Text className="text-indigo-600 font-semibold">Sign In</Text>
                        </TouchableOpacity>
                    </Link>
                </View>
            </View>
        </View>
    );
}
