import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, TextInput, TouchableOpacity, FlatList, Alert, ActivityIndicator, Modal } from 'react-native';
import api from '../../services/api';
import FontAwesome from '@expo/vector-icons/FontAwesome';
import { useFocusEffect } from 'expo-router';

interface Exchange {
    id: number;
    description: string;
}

interface NewKey {
    api_key: string;
    api_secret: string;
    exchange_provider_id: number | null;
}

interface Provider {
    id: number;
    description: string;
    name?: string;
}

export default function KeysScreen() {
    const [ownExchanges, setOwnExchanges] = useState<Exchange[]>([]);
    const [providers, setProviders] = useState<Provider[]>([]);
    const [loading, setLoading] = useState(true);
    const [modalVisible, setModalVisible] = useState(false);

    // New Key Form
    const [apiKey, setApiKey] = useState('');
    const [apiSecret, setApiSecret] = useState('');
    const [selectedProviderId, setSelectedProviderId] = useState<number | null>(null);
    const [submitting, setSubmitting] = useState(false);

    const loadData = async () => {
        try {
            setLoading(true);
            const [ownRes, providersRes] = await Promise.all([
                api.get('/exchanges/own'),
                api.get('/exchanges') // All available exchanges to add
            ]);
            setOwnExchanges(ownRes.data || []);
            const providersData = providersRes.data || [];
            console.log('Providers Data:', JSON.stringify(providersData, null, 2));
            setProviders(providersData);
        } catch (error: any) {
            console.error('Error loading keys data', error);
            const errorMessage = error.response?.data?.message || error.message || 'Failed to load exchanges';
            Alert.alert("Error", errorMessage);
        } finally {
            setLoading(false);
        }
    };

    useFocusEffect(
        useCallback(() => {
            loadData();
        }, [])
    );

    const handleDelete = async (item: Exchange) => {
        Alert.alert(
            "Delete Key",
            `Are you sure you want to delete ${item.description}?`,
            [
                { text: "Cancel", style: "cancel" },
                {
                    text: "Delete",
                    style: "destructive",
                    onPress: async () => {
                        try {
                            await api.delete(`/apikeys/${item.id}`);
                            Alert.alert("Success", "API key deleted successfully");
                            loadData();
                        } catch (e: any) {
                            console.error('Error deleting key:', e);
                            const errorMessage = e.response?.data?.message || e.response?.data?.error || e.message || "Failed to delete key";
                            Alert.alert("Error", errorMessage);
                        }
                    }
                }
            ]
        );
    };

    const handleAddKey = async () => {
        if (!apiKey || !apiSecret || !selectedProviderId) {
            Alert.alert("Error", "Please fill all fields and select an exchange");
            return;
        }

        setSubmitting(true);
        try {
            await api.post('/apikeys', {
                api_key: apiKey,
                api_secret: apiSecret,
                exchange_provider_id: selectedProviderId
            });
            Alert.alert("Success", "API key added successfully");
            setModalVisible(false);
            setApiKey('');
            setApiSecret('');
            setSelectedProviderId(null);
            loadData();
        } catch (e: any) {
            console.error('Error saving key:', e);
            const errorMessage = e.response?.data?.message || e.response?.data?.error || e.message || "Failed to save key";
            Alert.alert("Error", errorMessage);
        } finally {
            setSubmitting(false);
        }
    };

    const handleCloseModal = () => {
        setModalVisible(false);
        setApiKey('');
        setApiSecret('');
        setSelectedProviderId(null);
    };

    const renderItem = ({ item }: { item: Exchange }) => (
        <View className="bg-white dark:bg-gray-800 p-4 mb-3 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex-row justify-between items-center">
            <Text className="text-lg font-semibold text-gray-900 dark:text-white">{item.description}</Text>
            <TouchableOpacity onPress={() => handleDelete(item)} className="p-2 bg-red-100 rounded-full">
                <FontAwesome name="trash" size={20} color="#EF4444" />
            </TouchableOpacity>
        </View>
    );

    if (loading) {
        return (
            <View className="flex-1 justify-center items-center bg-gray-50 dark:bg-gray-900">
                <ActivityIndicator size="large" color="#4F46E5" />
            </View>
        );
    }

    return (
        <View className="flex-1 bg-gray-50 dark:bg-gray-900 p-4">
            <View className="flex-row justify-between items-center mb-6 mt-2">
                <Text className="text-2xl font-bold text-gray-900 dark:text-white">API Keys</Text>
                <TouchableOpacity
                    className="bg-indigo-600 px-4 py-2 rounded-lg"
                    onPress={() => setModalVisible(true)}
                >
                    <Text className="text-white font-semibold">Add Key</Text>
                </TouchableOpacity>
            </View>

            <FlatList
                data={ownExchanges}
                renderItem={renderItem}
                keyExtractor={(item) => item.id ? item.id.toString() : 'temp'}
                ListEmptyComponent={<Text className="text-gray-500 text-center mt-10">No API keys configured.</Text>}
            />

            {/* Add Key Modal */}
            <Modal
                animationType="slide"
                transparent={true}
                visible={modalVisible}
                onRequestClose={handleCloseModal}
            >
                <View className="flex-1 justify-center items-center bg-black/50 p-6">
                    <View className="bg-white dark:bg-gray-800 w-full rounded-2xl p-6 shadow-xl max-h-[90%]">
                        <Text className="text-xl font-bold text-gray-900 dark:text-white mb-4">Add New API Key</Text>

                        <Text className="text-gray-700 dark:text-gray-300 mb-2">Exchange</Text>
                        {providers.length === 0 ? (
                            <View className="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                <Text className="text-yellow-800 dark:text-yellow-200 text-sm">
                                    No exchanges available. Please contact support.
                                </Text>
                            </View>
                        ) : (
                            <View className="flex-row flex-wrap mb-4" style={{ gap: 8 }}>
                                {providers.map(p => (
                                    <TouchableOpacity
                                        key={p.id}
                                        onPress={() => setSelectedProviderId(p.id)}
                                        style={{
                                            paddingHorizontal: 12,
                                            paddingVertical: 8,
                                            borderRadius: 8,
                                            borderWidth: 1,
                                            backgroundColor: selectedProviderId === p.id ? '#E0E7FF' : '#F9FAFB',
                                            borderColor: selectedProviderId === p.id ? '#6366F1' : '#E5E7EB',
                                            marginBottom: 8,
                                            marginRight: 8,
                                        }}
                                    >
                                        <Text style={{
                                            color: selectedProviderId === p.id ? '#4338CA' : '#4B5563',
                                            fontWeight: selectedProviderId === p.id ? '600' : '400',
                                        }}>
                                            {p.description || p.name || `Exchange ${p.id}`}
                                        </Text>
                                    </TouchableOpacity>
                                ))}
                            </View>
                        )}

                        {selectedProviderId && (
                            <View className="mb-4 p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                                <Text className="text-indigo-700 dark:text-indigo-300 text-sm">
                                    Selected: {providers.find(p => p.id === selectedProviderId)?.description || providers.find(p => p.id === selectedProviderId)?.name}
                                </Text>
                            </View>
                        )}

                        <Text className="text-gray-700 dark:text-gray-300 mb-1">API Key</Text>
                        <TextInput
                            className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 mb-4"
                            placeholder="Key"
                            value={apiKey}
                            onChangeText={setApiKey}
                        />

                        <Text className="text-gray-700 dark:text-gray-300 mb-1">API Secret</Text>
                        <TextInput
                            className="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 mb-6"
                            placeholder="Secret"
                            secureTextEntry // Usually secrets are hidden
                            value={apiSecret}
                            onChangeText={setApiSecret}
                        />

                        <View className="flex-row justify-end" style={{ gap: 12, marginTop: 8 }}>
                            <TouchableOpacity
                                onPress={handleCloseModal}
                                className="px-4 py-2"
                                disabled={submitting}
                            >
                                <Text className="text-gray-600 dark:text-gray-400 font-medium">Cancel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                onPress={handleAddKey}
                                disabled={submitting || !selectedProviderId || providers.length === 0}
                                style={{
                                    backgroundColor: (!selectedProviderId || providers.length === 0) ? '#9CA3AF' : '#4F46E5',
                                    paddingHorizontal: 24,
                                    paddingVertical: 8,
                                    borderRadius: 8,
                                }}
                            >
                                {submitting ? <ActivityIndicator color="white" /> : <Text className="text-white font-semibold">Save</Text>}
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>
        </View>
    );
}
