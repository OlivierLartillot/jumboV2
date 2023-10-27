# CSV import errors

> IMPORTANT: In this csv file all rows not having a flight number are ignored

## Ignored rows

- Do not have a flight number ('Nº Vuelo/Transporte Origen')
- Have a flight number equal to XX9999
- Have the status cell ("Estado") text "cancelado" or "canceled" written exactly like this (case insensitive, ex: CanceLADO is accepted)


For these rows, the cell format is not important but the CSV format must be respected to not receive an error.

```shell
    example of CSV format for null
    - good: "2583699"|""|  the last |""| with separated pipes (|) is null but good
    - bad: "2583699","", the last ,"", with separated commas [,] is null but bad => return an error 
```


## Cells

This cells must not be null

- Localizadores: expected format => "jumboNumber, resevationNumber"
- Fecha/Hora Origen: expected format => "25/10/2023 16:32"
- Número pasajeros: expected format => "A: 20 N: 0 B: 0" 
- Traslado desde and Traslado hasta: No expected format but must not be null



## Upload related errors (1 to 9)

> <b>Execution stopped, data is not updated in the database.</b>

- Code import 1 - Token error, please refresh the page and start again.<br>
Why: This is a security protection. This can happen if you take too long or if someone outside the site tries to submit a file in any way other than the form.<br>
Solution: Refresh the page and try to resend the csv file.  This should be enough to stop this error. 

- Code import 2 - Error uploading the file. Error code : {{ error }}.<br>
Why: This is difficult to tell and depends on the error code returned in "errror code: ".<br>
Solution: Refresh the page and try to resend the csv file. If the problem persists, contact an administrator.

- Code import 3 - File not found.<br>
Why: The file may not have downloaded correctly. You should not encounter this error because the file is not uploaded to the server.

- Code import 4 - The file extension is not correct !<br>
Why: The extension of the file you are trying to send is not the correct one.<br>
Solution: Check if your file extension is .csv


## Errors inside the file (10 to 19)

> <b>Execution stopped, data is not updated in the database.</b>

<b>Warning near row { row number }</b> 

### Problèmes avec le numéro jumbo / numéro de client 
- Code import 10 - Error in your csv file on the <b>"location" cell</b>. The client is { client }, Flight number: { flight number }<br>
Why: The "localizadores" does not respect the defined format.<br>
Solution: The "localizadores" cell must be in this format: "jumbo number, reservation number" (the two values must be separated by a comma) 

- Code import 11 - Error in your csv file on a <b>"location" cell</b>. This client does not have a name in the csv file. Flight number: { flight number }<br>
Why: The "localizadores" does not respect the defined format.<br>
Solution: The "localizadores" cell must be in this format: "jumbo number, reservation number" (the two values must be separated by a comma) 

- Code import 12 - Error in your csv file on the "Fecha/Hora Origen" cell: <b>Wrong date formatting</b> The client is { client } Flight number: { flight number }<br>
Why: The date format is incorrect or elements are missing<br>
Solution: The date format must be "17/02/2024 16:32"

- Code import 13 - Error in your csv file on a "Fecha/Hora Origen" cell: <b>Wrong date formatting</b> This client does not have a name in the csv file. Flight number: { flight number }<br>
Why: The date format is incorrect or elements are missing<br>
Solution: The date format must be "17/02/2024 16:32"

- Code import 14 - Error in your csv file on the "Fecha/Hora Origen" cell: <b>Can not be null</b>. The client is { client } Flight number: { flight number }<br>
Why: the cell Fecha/Hora Origen is null<br>
Solution: The date format can't be null, it must be "17/02/2024 16:32"

- Code import 15 - Error in your csv file on a "Fecha/Hora Origen" cell: <b>Can not be null</b>. This client does not have a name in the csv file. Flight number: { flight number }<br>
Why: the cell Fecha/Hora Origen is null<br>
Solution: The date format can't be null, it must be "17/02/2024 16:32"

- Code import 16 - There are several arrival dates in this csv file. Make sure all arrival dates are on the same day! Flight number: { flight number } Row: {row number } Date: { date }<br>
Why: You can only have one arrival date per csv file. This error occurs when several different arrival dates are defined in the file. For example, if a client arrives on the 24th and another client on the 25th in the same csv file, this error will be returned.<br>
Solution: Make sure that all 'Fecha/Hora Origen' are on the same day in the csv file.

- Code import 17 - You cannot import a date older than 15 days.Date in csv file: { date }<br>
Why: To avoid a too long list of unassigned dates in the "Rep assignment menu", but also to avoid possible errors in the sent file, we have blocked the import only for the next 15 days. If you want to change this limit, contact an administrator.For example, if you enter a wrong csv file when sending next month, this error will be displayed and the data will not be updated<br>
Solution: Make sure that the “fecha hora” is less than the next fifteen days or asks an administrator to reduce or increase this period. 

- Code import 18 - There is a formatting error on the number of passengers. <br>
Why: The format is different from "A: 2 N: 1 B: 1"<br>
Solution: Make sure all “Número pasajeros” have the format  "A: 2 N: 1 B: 1", otherwise change the problematic cell and upload the csv file again.

- Code import 18.bis - Difference with 18: Number of passengers: <b> is NULL </b>
Why: The cell has no data so the format is different from "A: 2 N: 1 B: 1"<br>
Solution: Make sure all “Número pasajeros” have the format  "A: 2 N: 1 B: 1", otherwise change the problematic cell and upload the csv file again.

- Code import 19 - There is a formatting error on Airport or Hotel. Both cells must contain data while one is null. <br>
Why: Both cells must contain data while one is null.
Solution: Make sure all ”Traslado desde” and ”Traslado hasta” cells contain data. 
