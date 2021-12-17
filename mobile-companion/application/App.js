import { StatusBar } from 'expo-status-bar';
import { StyleSheet, Text, View } from 'react-native';

export default function App() {
  return (
    <View style={styles.container}>
      <Text style={styles.text}>TODO 😅</Text>
      <StatusBar style="auto" />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#4c566a',
    alignItems: 'center',
    justifyContent: 'center',
  },
  text: {
    color: '#d8dee9',
    fontSize: 42
  }
});
