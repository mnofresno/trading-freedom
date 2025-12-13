import React, { useState, useEffect } from 'react';
import { View, Text, TouchableOpacity, TextInput, Alert, ActivityIndicator, ScrollView } from 'react-native';
import { useAuth } from '../../context/AuthContext';
import api from '../../services/api';

export default function AccountScreen() {
    const { signOut } = useAuth()!;
    const [user, setUser] = useState({ name: '', email: '' });
    const [password, setPassword] = useState('');
    const [repeatPassword, setRepeatPassword] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        loadUser();
    }, []);

    const loadUser = async () => {
        try {
            const res = await api.get('/users/own');
            setUser(res.data);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    const handleUpdate = async () => {
        if (password && password !== repeatPassword) {
            Alert.alert("Error", "Passwords do not match");
            return;
        }

        setSaving(true);
        try {
            const payload: any = {
                name: user?.name,
                email: user?.email,
            };
            if (password) {
                payload.password = password;
            }
            // UserService.update sends POST to /users/own
            await api.post('/users/own', payload);
            Alert.alert("Success", "Profile updated");
            setPassword('');
            setRepeatPassword('');
        } catch (e) {
            Alert.alert("Error", "Failed to update profile");
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <View className="flex-1 justify-center items-center bg-gray-50 dark:bg-gray-900">
                <ActivityIndicator size="large" color="#4F46E5" />
            </View>
        );
    }

    return (
        <ScrollView className="flex-1 bg-gray-50 dark:bg-gray-900 p-6">
            <Text className="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Profile</Text>

            <View className="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm mb-6">
                <Text className="text-gray-700 dark:text-gray-300 mb-1">Name</Text>
                <TextInput
                    className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 mb-4"
                    value={user.name}
                    onChangeText={(t) => setUser({ ...user, name: t })}
                />

                <Text className="text-gray-700 dark:text-gray-300 mb-1">Email</Text>
                <TextInput
                    className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 mb-1"
                    value={user.email}
                    editable={false} // Email usually immutable or requires special flow
                />
                <Text className="text-xs text-gray-400 mb-4">Email cannot be changed.</Text>

                <View className="h-[1px] bg-gray-200 dark:bg-gray-700 my-4" />

                <Text className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</Text>

                <Text className="text-gray-700 dark:text-gray-300 mb-1">New Password</Text>
                <TextInput
                    className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 mb-4"
                    placeholder="Leave blank to keep current"
                    secureTextEntry
                    value={password}
                    onChangeText={setPassword}
                />

                <Text className="text-gray-700 dark:text-gray-300 mb-1">Confirm Password</Text>
                <TextInput
                    className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 mb-6"
                    placeholder="Repeat new password"
                    secureTextEntry
                    value={repeatPassword}
                    onChangeText={setRepeatPassword}
                />

                <TouchableOpacity
                    className="bg-indigo-600 py-3 rounded-lg items-center"
                    onPress={handleUpdate}
                    disabled={saving}
                >
                    {saving ? <ActivityIndicator color="white" /> : <Text className="text-white font-semibold">Update Profile</Text>}
                </TouchableOpacity>
            </View>

            <TouchableOpacity
                className="bg-red-500 py-3 rounded-lg mb-10"
                onPress={signOut}
            >
                <Text className="text-white text-center font-semibold">Log Out</Text>
            </TouchableOpacity>
        </ScrollView>
    );
}
