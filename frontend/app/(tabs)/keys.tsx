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
    name: string;
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
            const [ownRes, providersRes] = await Promise.all([
                api.get('/exchanges/own'),
                api.get('/exchanges') // All available exchanges to add
            ]);
            setOwnExchanges(ownRes.data);
            setProviders(providersRes.data);
        } catch (error) {
            console.log('Error loading keys data', error);
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
                            loadData();
                        } catch (e) {
                            Alert.alert("Error", "Failed to delete key");
                        }
                    }
                }
            ]
        );
    };

    const handleAddKey = async () => {
        if (!apiKey || !apiSecret || !selectedProviderId) {
            Alert.alert("Error", "Please fill all fields");
            return;
        }

        setSubmitting(true);
        try {
            await api.post('/apikeys', {
                api_key: apiKey,
                api_secret: apiSecret,
                exchange_provider_id: selectedProviderId
            });
            setModalVisible(false);
            setApiKey('');
            setApiSecret('');
            setSelectedProviderId(null);
            loadData();
        } catch (e) {
            console.error(e);
            Alert.alert("Error", "Failed to save key");
        } finally {
            setSubmitting(false);
        }
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
                onRequestClose={() => setModalVisible(false)}
            >
                <View className="flex-1 justify-center items-center bg-black/50 p-6">
                    <View className="bg-white dark:bg-gray-800 w-full rounded-2xl p-6 shadow-xl">
                        <Text className="text-xl font-bold text-gray-900 dark:text-white mb-4">Add New API Key</Text>

                        <Text className="text-gray-700 dark:text-gray-300 mb-2">Exchange</Text>
                        <View className="flex-row flex-wrap mb-4 gap-2">
                            {providers.map(p => (
                                <TouchableOpacity
                                    key={p.id}
                                    onPress={() => setSelectedProviderId(p.id)}
                                    className={`px-3 py-2 rounded-lg border ${selectedProviderId === p.id ? 'bg-indigo-100 border-indigo-500' : 'bg-gray-50 border-gray-200'}`}
                                >
                                    <Text className={selectedProviderId === p.id ? 'text-indigo-700 font-medium' : 'text-gray-600'}>{p.name}</Text>
                                </TouchableOpacity>
                            ))}
                        </View>

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

                        <View className="flex-row justify-end gap-3">
                            <TouchableOpacity
                                onPress={() => setModalVisible(false)}
                                className="px-4 py-2"
                            >
                                <Text className="text-gray-600 dark:text-gray-400 font-medium">Cancel</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                onPress={handleAddKey}
                                disabled={submitting}
                                className="bg-indigo-600 px-6 py-2 rounded-lg"
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
