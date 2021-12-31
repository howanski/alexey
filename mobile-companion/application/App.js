import React, { useState, useEffect } from "react";
import { Text, View, StyleSheet, Button } from "react-native";
import AsyncStorage from "@react-native-async-storage/async-storage";
import { BarCodeScanner } from "expo-barcode-scanner";

export default function App() {
    const [hasCemeraPermission, setHasCameraPermision] = useState(null);
    const [freezeScanner, setFreezeScanner] = useState(false);
    const [accessToken, setAccessToken] = useState(false);
    const [serverUri, setServerUri] = useState(false);
    const [defaultPath, setDefaultPath] = useState(false);
    const [needsScan, setNeedsScan] = useState(true);
    const [lastResponseCode, setLastResponseCode] = useState(0);
    useEffect(() => {
        (async () => {
            const { status } = await BarCodeScanner.requestPermissionsAsync();
            setHasCameraPermision(status === "granted");
        })();
    }, []);

    const persistToken = async (value) => {
        await AsyncStorage.setItem("accessToken", value);
        setAccessToken(value);
        setupRescanNeeded();
    };

    const retrieveToken = async () => {
        const value = await AsyncStorage.getItem("accessToken");
        setAccessToken(value);
        setupRescanNeeded();
    };

    const persistServerUri = async (value) => {
        await AsyncStorage.setItem("serverUri", value);
        setServerUri(value);
        setupRescanNeeded();
    };

    const retrieveServerUri = async () => {
        const value = await AsyncStorage.getItem("serverUri");
        setServerUri(value);
        setupRescanNeeded();
    };

    const persistDefaultPath = async (value) => {
        await AsyncStorage.setItem("defaultPath", value);
        setDefaultPath(value);
        setupRescanNeeded();
    };

    const retrieveDefaultPath = async () => {
        const value = await AsyncStorage.getItem("defaultPath");
        setDefaultPath(value);
        setupRescanNeeded();
    };

    const setupRescanNeeded = async () => {
        setNeedsScan(!(defaultPath && serverUri && accessToken));
    };

    const fetchServerState = () => {
        let fullPath = serverUri + defaultPath;
        console.log("-- STORED CREDENTIALS --");
        console.log(fullPath);
        console.log(accessToken);
        console.log("-- STORED CREDENTIALS END --");
        if (fullPath && accessToken) {
            setFreezeScanner(true);
            fetch(fullPath, {
                method: "GET",
                headers: { "X-ALEXEY-SECRET": accessToken },
            })
                .then((response) => response.json())
                .then((jsonResponse) => {
                    console.log("--- SERVER RESPONSE ---");
                    console.log(jsonResponse);
                    console.log("--- SERVER RESPONSE END ---");
                    if (jsonResponse.code === 200) {
                        setNeedsScan(false);
                    } else if(jsonResponse.code === 401) {
                        console.log('odrzut');
                        persistToken('');
                    }
                    setLastResponseCode(jsonResponse.code);
                    setFreezeScanner(false);
                })
                .catch((error) => {
                    console.error(error);
                    console.log('END OF RESPONSE ERROR');
                    setNeedsScan(true);
                });
        } else {
            setFreezeScanner(false);
            setNeedsScan(true);
        }
    };

    const handleBarCodeScanned = ({ type, data }) => {
        if (!freezeScanner) {
            let obj = JSON.parse(data);
            console.log("---- QR DATA ----");
            console.log(obj);
            console.log("---- QR DATA END ----");
            //
            if (obj.accessToken) {
                persistToken(obj.accessToken);
            }
            if (obj.serverUri) {
                persistServerUri(obj.serverUri);
            }
            if (obj.defaultPath) {
                persistDefaultPath(obj.defaultPath);
            }
            //
            fetchServerState();
        }
    };

    if (hasCemeraPermission === null) {
        return <Text>Requesting for camera permission</Text>;
    }
    if (hasCemeraPermission === false) {
        return <Text>No access to camera</Text>;
    }

    retrieveToken();
    retrieveServerUri();
    retrieveDefaultPath();
    console.log("Needs scan =");
    console.log(needsScan);
    if (lastResponseCode === 0) {
        setLastResponseCode(999);
        fetchServerState();
    }
    // fetchServerState(); //TODO: resolve re-render looping

    return (
        <View style={styles.container}>
            {needsScan && (
                <BarCodeScanner
                    onBarCodeScanned={handleBarCodeScanned}
                    style={StyleSheet.absoluteFillObject}
                />
            )}
            <Text style={styles.text}>{accessToken}</Text>
            <Text style={styles.text}>{serverUri}</Text>
            <Text style={styles.text}>{defaultPath}</Text>
            <Text style={styles.text}>{lastResponseCode}</Text>
            <Button title="refresh" onPress={fetchServerState}></Button>
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
        color: "#cf0a3b",
        fontSize: 42,
    },
    input: {
        color: "#cf0a3b",
        height: 40,
        borderColor: "gray",
        borderWidth: 1,
    },
});
