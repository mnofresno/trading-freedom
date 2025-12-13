import React, { useEffect, useState, useCallback } from 'react';
import { StyleSheet, FlatList, ActivityIndicator, TouchableOpacity, RefreshControl, Image } from 'react-native';
import { Text, View } from 'react-native';
import api from '../../services/api';
import { useAuth } from '../../context/AuthContext';
import { useFocusEffect } from 'expo-router';
// Using NativeWind for styling, removing StyleSheet where possible or mixing

interface Balance {
  MONEDA: string;
  DISPONIBLE: string;
  EN_ORDENES: string;
  TOTAL: number;
}

interface Exchange {
  id: number;
  description: string;
}

export default function DashboardScreen() {
  const [balances, setBalances] = useState<Balance[]>([]);
  const [exchanges, setExchanges] = useState<Exchange[]>([]);
  const [selectedExchange, setSelectedExchange] = useState<Exchange | null>(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const auth = useAuth();

  const loadData = async () => {
    try {
      const exchangeRes = await api.get('/exchanges/own');
      const ownExchanges = exchangeRes.data;
      setExchanges(ownExchanges);

      if (ownExchanges.length > 0) {
        const exchangeToLoad = selectedExchange || ownExchanges[0];
        if (!selectedExchange) setSelectedExchange(exchangeToLoad);

        await loadBalances(exchangeToLoad.id);
      } else {
        setBalances([]);
      }
    } catch (error) {
      console.log('Error loading dashboard data', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const loadBalances = async (exchangeId: number) => {
    try {
      const balanceRes = await api.get(`/balances/${exchangeId}`);
      // Legacy API structure often wraps result. result.balances? or just array?
      // services.js: self.Balances = result; (where result is from callback)
      // DashCtrl implies `self.Balances.balances` array.
      // Let's assume response.data is the object containing { balances: [...] }
      setBalances(balanceRes.data.balances || []);
    } catch (e) {
      console.error("Error loading balances", e);
    }
  }

  useFocusEffect(
    useCallback(() => {
      loadData();
    }, [])
  );

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const renderItem = ({ item }: { item: Balance }) => (
    <View className="bg-white dark:bg-gray-800 p-4 mb-3 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex-row justify-between items-center">
      <View className="flex-row items-center">
        {/* Placeholder for coin icon */}
        <View className="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 rounded-full justify-center items-center mr-3">
          <Text className="text-indigo-600 dark:text-indigo-300 font-bold">{item.MONEDA.substring(0, 1)}</Text>
        </View>
        <View>
          <Text className="text-lg font-bold text-gray-900 dark:text-white">{item.MONEDA}</Text>
          <Text className="text-xs text-gray-500 dark:text-gray-400">Available: {item.DISPONIBLE}</Text>
        </View>
      </View>
      <View>
        <Text className="text-lg font-semibold text-gray-900 dark:text-white text-right">{item.TOTAL}</Text>
        <Text className="text-xs text-gray-400 text-right">In Orders: {item.EN_ORDENES}</Text>
      </View>
    </View>
  );

  if (loading && !refreshing) {
    return (
      <View className="flex-1 justify-center items-center bg-gray-50 dark:bg-gray-900">
        <ActivityIndicator size="large" color="#4F46E5" />
      </View>
    );
  }

  return (
    <View className="flex-1 bg-gray-50 dark:bg-gray-900 p-4">
      <View className="flex-row justify-between items-center mb-6 mt-2">
        <Text className="text-2xl font-bold text-gray-900 dark:text-white">My Wallet</Text>
        {/* Simple Exchange Selector if multiple */}
        {exchanges.length > 1 && selectedExchange && (
          <TouchableOpacity onPress={() => {/* Toggle Selector logic later */ }}>
            <Text className="text-indigo-600 font-semibold">{selectedExchange.description}</Text>
          </TouchableOpacity>
        )}
      </View>

      {balances.length === 0 ? (
        <View className="flex-1 justify-center items-center">
          <Text className="text-gray-500 text-lg">No balances found.</Text>
          <Text className="text-gray-400 text-sm mt-2">Add an API Key in the Settings tab.</Text>
        </View>
      ) : (
        <FlatList
          data={balances}
          renderItem={renderItem}
          keyExtractor={(item) => item.MONEDA}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
          showsVerticalScrollIndicator={false}
        />
      )}
    </View>
  );
}
