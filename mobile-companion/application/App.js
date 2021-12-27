import { StatusBar } from "expo-status-bar";
import React, { useState } from "react";
import { StyleSheet, Text, TextInput, View, Button } from "react-native";

export default function App() {
    const [serverUri, setServerUri] = useState("https://alexey_dev");
    const [serverSecret, setServerSecret] = useState("TotallySecret!");
    const [mainResult, setMainResult] = useState("");
    return (
        <View style={styles.container}>
            <Text style={styles.text}>Server URI:</Text>
            <TextInput
                style={styles.input}
                defaultValue={serverUri}
                onChangeText={(text) => setServerUri(text)}
            />
            <Text style={styles.text}>Server Secret:</Text>
            <TextInput
                style={styles.input}
                defaultValue={serverSecret}
                onChangeText={(text) => setServerSecret(text)}
            />
            <Button
                onPress={() => {
                    setMainResult("--- 😅 ---");
                    fetch(serverUri + "/api/dashboard", {
                        method: "GET",
                        headers: { "X-ALEXEY-SECRET": serverSecret },
                    })
                        .then((response) => response.json())
                        .then((json) => {
                            setMainResult(json.code + " : " + json.message);
                        })
                        .catch((error) => {
                            console.error(error);
                        });
                }}
                title={"Connect"}
            />
            <Text style={styles.text}>{mainResult}</Text>
            <StatusBar style="auto" />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: "#434c5e",
        alignItems: "center",
        justifyContent: "center",
    },
    text: {
        color: "#d8dee9",
        fontSize: 42,
    },
    input: {
        color: "#d8dee9",
        height: 40,
        borderColor: "gray",
        borderWidth: 1,
    },
});
