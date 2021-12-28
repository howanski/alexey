import React, { useState, useEffect } from "react";
import { Text, View, StyleSheet, Button } from "react-native";
import { BarCodeScanner } from "expo-barcode-scanner";

export default function App() {
    const [hasPermission, setHasPermission] = useState(null);
    const [serverUri, setServerUri] = useState("");
    const [serverSecret, setServerSecret] = useState("");
    const [defaultPath, setDefaultPath] = useState("");
    const [serverResponseCode, setServerResponseCode] = useState(401);
    const [serverResponseMessage, setServerResponseMessage] = useState("XYZ");
    const [freezeScanner, setFreezeScanner] = useState(false);
    useEffect(() => {
        (async () => {
            const { status } = await BarCodeScanner.requestPermissionsAsync();
            setHasPermission(status === "granted");
        })();
    }, []);

    const handleBarCodeScanned = ({ type, data }) => {
        if (!freezeScanner) {
            let obj = JSON.parse(data);
            setServerUri(obj.server);
            setServerSecret(obj.token);
            setDefaultPath(obj.path);
            let fullPath = serverUri + defaultPath;
            console.log(fullPath);
            if (fullPath) {
                setFreezeScanner(true);
                fetch(fullPath, {
                    method: "GET",
                    headers: { "X-ALEXEY-SECRET": serverSecret },
                })
                    .then((response) => response.json())
                    .then((jsonResponse) => {
                        setServerResponseCode(jsonResponse.code);
                        setServerResponseMessage(jsonResponse.message);

                        // setServerResponseObject(jsonResponse);
                        console.log(jsonResponse);
                        setFreezeScanner(false);
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            } else {
                setFreezeScanner(false);
            }
        }
    };

    if (hasPermission === null) {
        return <Text>Requesting for camera permission</Text>;
    }
    if (hasPermission === false) {
        return <Text>No access to camera</Text>;
    }

    return (
        <View style={styles.container}>
            {serverResponseCode == 401 && (
                <BarCodeScanner
                    onBarCodeScanned={handleBarCodeScanned}
                    style={StyleSheet.absoluteFillObject}
                />
            )}
            {!(serverResponseCode === 401) && (
                <Text style={styles.text}>{serverResponseMessage}</Text>
            )}
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
